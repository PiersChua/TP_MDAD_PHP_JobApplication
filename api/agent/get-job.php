<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../schema/job.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_GET["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_GET, $getJobSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $jobId] = [$_GET["userId"], $_GET["jobId"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $findExistingJobStmt = $db->getConnection()->prepare("
            SELECT jobs.* FROM jobs
            WHERE jobs.jobId = ?
        ");
        $findExistingJobStmt->bind_param("s", $jobId);
        $findExistingJobStmt->execute();
        $result = $findExistingJobStmt->get_result();
        $findExistingJobStmt->close();
        $job = $result->fetch_assoc(); // fetches a single row
        if ($job === null) {
            http_response_code(404);
            echo json_encode(array("message" => "Job not found"));
            exit();
        }
        echo json_encode(array("data" => $job));
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
