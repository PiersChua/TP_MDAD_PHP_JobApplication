<?php
require_once __DIR__ . "/../../schema/agency-application.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/userValidator.php";


$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);

if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_POST, $updateAgencyApplicationSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}

[$userId, $agencyApplicationId, $status] = [$_POST["userId"], $_POST["agencyApplicationId"], $_POST["status"]];
if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(array("message" => "Status does not exist"));
    exit();
}

// Verify token
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());
        // check the application exists, along with the Job Seeker who created the application
        $findExistingAgencyApplicationStmt = $db->getConnection()->prepare("
        SELECT agency_applications.*, 
        users.userId as jobSeekerId FROM agency_applications
        INNER JOIN users ON agency_applications.userId = users.userId 
        WHERE agencyApplicationId=? AND users.role='Job Seeker'");
        $findExistingAgencyApplicationStmt->bind_param("s", $agencyApplicationId);
        $findExistingAgencyApplicationStmt->execute();
        $result = $findExistingAgencyApplicationStmt->get_result();
        $findExistingAgencyApplicationStmt->close();
        $agencyApplication = $result->fetch_assoc();
        if ($agencyApplication === null) {
            http_response_code(404);
            echo json_encode(array("message" => "Agency application not found"));
            exit();
        }
        if ($agencyApplication["jobSeekerId"] === "") {
            http_response_code(404);
            echo json_encode(array("message" => "Job Seeker not found"));
            exit();
        }
        // Rejecting agency application
        if ($status == "REJECTED") {
            // Rejecting an application should also delete the job application from the database
            $rejectApplicationStmt = $db->getConnection()->prepare("
            UPDATE agency_applications
            SET status='REJECTED' WHERE agencyApplicationId = ?
            ");
            $rejectApplicationStmt->bind_param("s", $agencyApplicationId);
            $rejectApplicationStmt->execute();
            $rejectApplicationStmt->close();
            echo json_encode(array("message" => "Agency application rejected"));
            exit();
        }

        $email = $agencyApplication["email"];
        $phoneNumber = $agencyApplication["phoneNumber"];
        $name = $agencyApplication["name"];
        $findDuplicateStmt = $db->getConnection()->prepare("
           SELECT 
            (SELECT COUNT(*) FROM users WHERE email = ?)
            + (SELECT COUNT(*) FROM agencies WHERE email = ?) ,
            (SELECT COUNT(*) FROM users WHERE phoneNumber = ?)
            + (SELECT COUNT(*) FROM agencies WHERE phoneNumber = ?),
            (SELECT COUNT(*) FROM agencies WHERE name=?)
        ");
        $findDuplicateStmt->bind_param("sssss", $email, $email, $phoneNumber, $phoneNumber, $name);
        $findDuplicateStmt->execute();
        $findDuplicateStmt->bind_result($emailCount, $phoneNumberCount, $nameCount);
        $findDuplicateStmt->fetch();
        $findDuplicateStmt->close();
        if ($emailCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Email is already in use"));
            exit();
        }
        if ($phoneNumberCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Phone Number is already in use"));
            exit();
        }
        if ($nameCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Name is already in use"));
            exit();
        }

        /**
         *  Database Transaction
         * 1. Create a new agency
         * 2. Promote the user's role
         * 3. Update agency application status
         * 4. Delete the job applications associated with the user (if any)
         * 5. Delete the favourite jobs associated with the user (if any)
         * 6. Reject any residual agency applications associated with the user (if any)
         */
        try {
            $image =
                $agencyApplication["image"] ?? null;
            $connection = $db->getConnection();
            $connection->begin_transaction();
            $createAgencyStmt = $connection->prepare("INSERT INTO agencies (name, email, phoneNumber, address, image, userId) VALUES(?,?,?,?,?,?) ");
            $createAgencyStmt->bind_param("ssssbs", $agencyApplication["name"], $agencyApplication["email"], $agencyApplication["phoneNumber"], $agencyApplication["address"], $image, $agencyApplication["userId"]);
            if ($image !== null) {
                $createAgencyStmt->send_long_data(4, $image);
            }
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

            $deleteFavouriteJobsStmt = $connection->prepare("
            DELETE FROM favourite_jobs
            WHERE userId=?
            ");
            $deleteFavouriteJobsStmt->bind_param("s", $agencyApplication["userId"]);
            $deleteFavouriteJobsStmt->execute();
            $deleteFavouriteJobsStmt->close();

            $deleteJobApplicationsStmt = $connection->prepare("
            DELETE FROM job_applications
            WHERE userId=?
            ");
            $deleteJobApplicationsStmt->bind_param("s", $agencyApplication["userId"]);
            $deleteJobApplicationsStmt->execute();
            $deleteJobApplicationsStmt->close();

            $deleteAgencyApplicationsStmt = $connection->prepare("
            UPDATE agency_applications
            SET status='REJECTED'
            WHERE userId=? AND agencyApplicationId!=?
            ");
            $deleteAgencyApplicationsStmt->bind_param("ss", $agencyApplication["userId"], $agencyApplication["agencyApplicationId"]);
            $deleteAgencyApplicationsStmt->execute();
            $deleteAgencyApplicationsStmt->close();

            // commit the transaction
            $connection->commit();
            echo json_encode(array("message" => "Agency application accepted"));
        } catch (Exception $e) {
            $connection->rollback();
            http_response_code(500);
            echo json_encode(array("message" => "Transaction failed: " . $e->getMessage()));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage()));
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to connect to the database"));
    $db->close();
}
