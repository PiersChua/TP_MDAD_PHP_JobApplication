<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/user.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_POST, $deleteUserSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $userIdToBeDeleted] = [$_POST["userId"], $_POST["userIdToBeDeleted"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {

        /**
         *  Database Transaction
         * 1. Delete associated jobs (if any)
         * 2. Delete associated favourite jobs (if any)
         * 3. Delete associated agency applications (if any)
         * 4. Delete associated job applications (if any)
         * 5. Delete associated agencies (if any)
         * 6. Delete the user
         */
        try {
            $connection = $db->getConnection();
            $connection->begin_transaction();

            $deleteJobsStmt = $connection->prepare("
            DELETE FROM jobs
            WHERE userId=?
            ");
            $deleteJobsStmt->bind_param("s", $userIdToBeDeleted);
            $deleteJobsStmt->execute();
            $deleteJobsStmt->close();

            $deleteFavouriteJobsStmt = $connection->prepare("
            DELETE FROM favourite_jobs
            WHERE userId=?
            ");
            $deleteFavouriteJobsStmt->bind_param("s", $userIdToBeDeleted);
            $deleteFavouriteJobsStmt->execute();
            $deleteFavouriteJobsStmt->close();

            $deleteAgencyApplicationsStmt = $connection->prepare("
            DELETE FROM agency_applications
            WHERE userId=?
            ");
            $deleteAgencyApplicationsStmt->bind_param("s", $userIdToBeDeleted);
            $deleteAgencyApplicationsStmt->execute();
            $deleteAgencyApplicationsStmt->close();

            $deleteJobApplicationsStmt = $connection->prepare("
            DELETE FROM job_applications
            WHERE userId=?
            ");
            $deleteJobApplicationsStmt->bind_param("s", $userIdToBeDeleted);
            $deleteJobApplicationsStmt->execute();
            $deleteJobApplicationsStmt->close();

            $deleteAgencyStmt = $connection->prepare("
            DELETE FROM agencies
            WHERE userId=?
            ");
            $deleteAgencyStmt->bind_param("s", $userIdToBeDeleted);
            $deleteAgencyStmt->execute();
            $deleteAgencyStmt->close();

            $deleteUserStmt = $db->getConnection()->prepare("
            DELETE FROM users
            WHERE userId=?
            ");
            $deleteUserStmt->bind_param("s", $userIdToBeDeleted);
            $deleteUserStmt->execute();
            $deleteUserStmt->close();

            $connection->commit();
            echo json_encode(array("message" => "User has been deleted, along with it's associated records"));
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
