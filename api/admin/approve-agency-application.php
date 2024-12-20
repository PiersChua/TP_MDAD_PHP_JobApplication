<?php
require_once __DIR__ . "/../../schema/agency-application.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);

if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required", "type" => "Error"));
    exit();
}

if (!isset($_POST["agencyApplicationId"])) {
    http_response_code(400);
    echo json_encode(array("message" => "AgencyApplicationId is required", "type" => "Error"));
    exit();
}

[$userId, $agencyApplicationId] = [$_POST["userId"], $_POST["agencyApplicationId"]];

// Verify token
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        // check if admin exists
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT COUNT(*) from users WHERE userId=? AND role='Admin'");
        $findExistingUserStmt->bind_param("s", $userId);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($userCount);
        $findExistingUserStmt->fetch();
        $findExistingUserStmt->close();
        if ($userCount === 0) {
            http_response_code(404);
            echo json_encode(array("message" => "User not found", "type" => "Error"));
            exit();
        }

        // check the application exists, along with the Job Seeker who created the application
        $findExistingAgencyApplicationStmt = $db->getConnection()->prepare("
        SELECT agency_applications.*,users.userId as jobSeekerId FROM agency_applications
        INNER JOIN users ON agency_applications.userId = users.userId 
        WHERE agencyApplicationId=? AND users.role='Job Seeker'");
        $findExistingAgencyApplicationStmt->bind_param("s", $agencyApplicationId);
        $findExistingAgencyApplicationStmt->execute();
        $result = $findExistingAgencyApplicationStmt->get_result();
        $findExistingAgencyApplicationStmt->close();
        $agencyApplication = $result->fetch_assoc();
        if ($agencyApplication === null) {
            http_response_code(404);
            echo json_encode(array("message" => "Agency application not found", "type" => "Error"));
            exit();
        }
        if ($agencyApplication["jobSeekerId"] === "") {
            http_response_code(400);
            echo json_encode(array("message" => "Job Seeker not found", "type" => "Error"));
            exit();
        }

        // check the db if an agency already exists, combine the result with UNION ALL
        $email = $agencyApplication["email"];
        $phoneNumber = $agencyApplication["phoneNumber"];
        $name = $agencyApplication["name"];
        $findDuplicateStmt = $db->getConnection()->prepare("
            SELECT COUNT(*) 
            FROM (
                SELECT email FROM users WHERE email = ? OR phoneNumber = ?
                UNION ALL
                SELECT email FROM agencies WHERE email = ? OR phoneNumber = ? OR name = ?
            ) as combined
        ");
        $findDuplicateStmt->bind_param("sssss", $email, $phoneNumber, $email, $phoneNumber, $name);
        $findDuplicateStmt->execute();
        $findDuplicateStmt->bind_result($duplicateCount);
        $findDuplicateStmt->fetch();
        $findDuplicateStmt->close();
        if ($duplicateCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Email, phone number or agency name is already in use", "type" => "Error"));
            exit();
        }

        /**
         *  Database Transaction
         * 1. Create a new agency
         * 2. Promote the user's role
         * 3. Update agency application status
         * 4. Delete the job applications associated with the user (if any)
         */
        try {
            $connection = $db->getConnection();
            $connection->begin_transaction();
            $createAgencyStmt = $connection->prepare("INSERT INTO agencies (name, email, phoneNumber, address, userId) VALUES(?,?,?,?,?) ");
            $createAgencyStmt->bind_param("sssss", $agencyApplication["name"], $agencyApplication["email"], $agencyApplication["phoneNumber"], $agencyApplication["address"], $agencyApplication["userId"]);
            $createAgencyStmt->execute();
            $createAgencyStmt->close();

            $promoteRoleStmt = $connection->prepare("
            UPDATE users 
            SET role='Agency Admin'
            WHERE userId=?");
            $promoteRoleStmt->bind_param("s", $agencyApplication["userId"]);
            $promoteRoleStmt->execute();
            $promoteRoleStmt->close();

            $updateApplicationStatusStmt = $connection->prepare("
            UPDATE agency_applications
            SET status='ACCEPTED'
            WHERE agencyApplicationId=?
            ");
            $updateApplicationStatusStmt->bind_param("s", $agencyApplication["agencyApplicationId"]);
            $updateApplicationStatusStmt->execute();
            $updateApplicationStatusStmt->close();

            $deleteJobApplicationsStmt = $connection->prepare("
            DELETE FROM job_applications
            WHERE userId=?
            ");
            $deleteJobApplicationsStmt->bind_param("s", $agencyApplication["userId"]);
            $deleteJobApplicationsStmt->execute();
            $deleteJobApplicationsStmt->close();

            // commit the transaction
            $connection->commit();
            echo json_encode(array("message" => "Agency has been created", "type" => "Success"));
        } catch (Exception $e) {
            $connection->rollback();
            http_response_code(500);
            echo json_encode(array("message" => "Transaction failed: " . $e->getMessage(), "type" => "Error"));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage(), "type" => "Error"));
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to connect to the database", "type" => "Error"));
    $db->close();
}
