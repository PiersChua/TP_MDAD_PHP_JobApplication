<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/stringUtils.php";
require_once __DIR__ . "/../../utils/userValidator.php";


$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_POST, $addAgentSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $email, $agencyAdminUserId] = [$_POST["userId"], StringUtils::lowercaseEmail($_POST["email"]), $_POST["agencyAdminUserId"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());
        $findExistingAgencyAdminStmt = $db->getConnection()->prepare("SELECT agencyId from agencies WHERE userId=?");
        $findExistingAgencyAdminStmt->bind_param("s", $agencyAdminUserId);
        $findExistingAgencyAdminStmt->execute();
        $findExistingAgencyAdminStmt->bind_result($agencyId);
        $findExistingAgencyAdminStmt->fetch();
        $findExistingAgencyAdminStmt->close();
        if ($agencyId === null) {
            http_response_code(404);
            echo json_encode(array("message" => "Agency Admin not found"));
            exit();
        }

        $findJobSeekerStmt = $db->getConnection()->prepare("SELECT userId from users WHERE email=?");
        $findJobSeekerStmt->bind_param("s", $email);
        $findJobSeekerStmt->execute();
        $findJobSeekerStmt->bind_result($jobSeekerUserId);
        $findJobSeekerStmt->fetch();
        $findJobSeekerStmt->close();
        if ($jobSeekerUserId === null) {
            http_response_code(404);
            echo json_encode(array("message" => "Job Seeker not found"));
            exit();
        }

        /**
         *  Database Transaction
         * 1. Delete associated favourite jobs (if any)
         * 2. Delete associated job applications (if any)
         * 3. Delete associated agency applications (if any)
         * 4. Update the role to Agent
         */
        try {
            $connection = $db->getConnection();
            $connection->begin_transaction();

            $deleteFavouriteJobsStmt = $connection->prepare("
            DELETE FROM favourite_jobs
            WHERE userId=?
            ");
            $deleteFavouriteJobsStmt->bind_param("s", $jobSeekerUserId);
            $deleteFavouriteJobsStmt->execute();
            $deleteFavouriteJobsStmt->close();

            $deleteJobApplicationsStmt = $connection->prepare("
            DELETE FROM job_applications
            WHERE userId=?
            ");
            $deleteJobApplicationsStmt->bind_param("s", $jobSeekerUserId);
            $deleteJobApplicationsStmt->execute();
            $deleteJobApplicationsStmt->close();

            $deleteAgencyApplicationsStmt = $connection->prepare("
            DELETE FROM agency_applications
            WHERE userId=?
            ");
            $deleteAgencyApplicationsStmt->bind_param("s", $jobSeekerUserId);
            $deleteAgencyApplicationsStmt->execute();
            $deleteAgencyApplicationsStmt->close();

            $promoteUserStmt = $db->getConnection()->prepare("
            UPDATE users
            SET role='Agent', agencyId=?
            WHERE users.userId=? AND users.role='Job Seeker'");
            $promoteUserStmt->bind_param("ss", $agencyId, $jobSeekerUserId);
            $promoteUserStmt->execute();
            $promoteUserStmt->close();

            $connection->commit();
            echo json_encode(array("message" => "Agent added"));
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
    echo json_encode(array("message" => "Failed to connect to database"));
    $db->close();
}
