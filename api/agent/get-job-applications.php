<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../schema/job-application.php";
require_once __DIR__ . "/../../utils/userValidator.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_GET["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_GET, $getJobApplicationsSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $jobId, $agentUserId] = [$_GET["userId"], $_GET["jobId"], $_GET["agentUserId"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $db->getConnection());
        $findJobApplicationsStmt = $db->getConnection()->prepare("
        SELECT users.fullName as user_full_name, users.email as user_email,users.phoneNumber as user_phone_number, users.userId, job_applications.status, job_applications.updatedAt FROM job_applications
        INNER JOIN jobs ON jobs.jobId = job_applications.jobId
        INNER JOIN users ON job_applications.userId = users.userId
        WHERE job_applications.jobId=? AND jobs.userId=?
        ORDER BY job_applications.createdAt DESC
        ");
        $findJobApplicationsStmt->bind_param("ss", $jobId, $agentUserId);
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
