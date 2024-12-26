<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/job-application.php";
require_once __DIR__ . "/../../utils/validation.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_POST, $updateJobApplicationSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $jobId, $jobApplicationUserId, $status] = [$_POST["userId"], $_POST["jobId"], $_POST["jobApplicationUserId"], $_POST["status"]];
if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(array("message" => "Status does not exist"));
    exit();
}
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $updateJobApplicationStmt = $db->getConnection()->prepare("
       UPDATE job_applications
       SET status=?
       WHERE jobId=? AND userId=?
        ");
        $updateJobApplicationStmt->bind_param("sss", $status, $jobId, $jobApplicationUserId);
        $updateJobApplicationStmt->execute();
        $updateJobApplicationStmt->close();
        echo json_encode(array("message" => "Job application " . strtolower($status)));
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
