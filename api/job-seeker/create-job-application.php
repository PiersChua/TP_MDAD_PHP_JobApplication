<?php
require_once __DIR__ . "/../../schema/job-application.php";
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
$result = Validation::validateSchema($_POST, $createJobApplicationSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $jobId] = [$_POST["userId"], $_POST["jobId"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $db->getConnection());

        // check if job exists
        $findExistingJobStmt = $db->getConnection()->prepare("SELECT COUNT(*) FROM jobs WHERE jobId=?");
        $findExistingJobStmt->bind_param("s", $jobId);
        $findExistingJobStmt->execute();
        $findExistingJobStmt->bind_result($jobCount);
        $findExistingJobStmt->fetch();
        $findExistingJobStmt->close();
        if ($jobCount === 0) {
            http_response_code(404);
            echo json_encode(array("message" => "Job not found"));
            exit();
        }

        // check if job application exists
        $findExistingJobApplicationStmt = $db->getConnection()->prepare("SELECT COUNT(*) FROM job_applications WHERE jobId=? AND userId=?");
        $findExistingJobApplicationStmt->bind_param("ss", $jobId, $userId);
        $findExistingJobApplicationStmt->execute();
        $findExistingJobApplicationStmt->bind_result($jobApplicationCount);
        $findExistingJobApplicationStmt->fetch();
        $findExistingJobApplicationStmt->close();
        if ($jobApplicationCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Job Application already submitted"));
            exit();
        }

        // create job application
        $createApplicationStmt = $db->getConnection()->prepare("INSERT INTO job_applications (userId, jobId) VALUES (?,?)");
        $createApplicationStmt->bind_param("ss", $userId, $jobId);
        $createApplicationStmt->execute();
        $createApplicationStmt->close();
        echo json_encode(array("message" => "Job application submitted"));
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
