<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_GET["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
if (!isset($_GET["jobId"])) {
    http_response_code(400);
    echo json_encode(array("message" => "JobId is required"));
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
        $findJobApplicationsStmt = $db->getConnection()->prepare("
        SELECT users.fullName as user_full_name, users.email as user_email,users.phoneNumber as user_phone_number, users.userId FROM job_applications
        INNER JOIN jobs ON jobs.jobId = job_applications.jobId
        INNER JOIN users ON job_applications.userId = users.userId
        WHERE job_applications.jobId=? AND jobs.userId=?
        ");
        $findJobApplicationsStmt->bind_param("ss", $jobId, $userId);
        $findJobApplicationsStmt->execute();
        $result = $findJobApplicationsStmt->get_result();
        $findJobApplicationsStmt->close();
        $favouriteJobs = $result->fetch_all(MYSQLI_ASSOC); // Fetches all rows as an associative array
        echo json_encode(array("data" => $favouriteJobs));
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
