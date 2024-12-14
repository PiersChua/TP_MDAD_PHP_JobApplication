<?php
require_once __DIR__ . "/../../schema/job-application.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
$result = Validation::validateSchema($_POST, $jobApplicationSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result, "type" => "Error"));
    exit();
}
// todo: change this to extract the userId from jwt token from headers
[$userId, $jobId] = [$_POST["userId"], $_POST["jobId"]];

$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        // check if user exists
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT COUNT(*) from users WHERE userId=? AND role='JobSeeker'");
        $findExistingUserStmt->bind_param("s", $userId);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($userCount);
        $findExistingUserStmt->fetch();
        $findExistingUserStmt->close();
        if ($userCount === 0) {
            http_response_code(404);
            echo json_encode(array("message" => "User not found", "type" => "Error"));
            $db->close();
            exit();
        }

        // check if job exists
        $findExistingJobStmt = $db->getConnection()->prepare("SELECT COUNT(*) FROM jobs WHERE jobId=?");
        $findExistingJobStmt->bind_param("s", $jobId);
        $findExistingJobStmt->execute();
        $findExistingJobStmt->bind_result($jobCount);
        $findExistingJobStmt->fetch();
        $findExistingJobStmt->close();
        if ($jobCount === 0) {
            http_response_code(404);
            echo json_encode(array("message" => "Job not found", "type" => "Error"));
            $db->close();
            exit();
        }

        // create job application
        $createApplicationStmt = $db->getConnection()->prepare("INSERT INTO job_applications (userId, jobId) VALUES (?,?)");
        $createApplicationStmt->bind_param("ss", $userId, $jobId);
        $createApplicationStmt->execute();
        $createApplicationStmt->close();
        echo json_encode(array("message" => "Job application submitted", "type" => "Success"));
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
