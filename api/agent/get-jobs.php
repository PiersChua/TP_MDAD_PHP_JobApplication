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
$result = Validation::validateSchema($_GET, $getJobsSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $agentUserId] = [$_GET["userId"], $_GET["agentUserId"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            // extract the jobs based on the limit given
            $limit = $_GET["limit"];
            $findJobsStmt = $db->getConnection()->prepare("
             SELECT jobs.*, COUNT(favourite_jobs.userId) AS favourite_job_count FROM jobs 
            LEFT JOIN favourite_jobs ON jobs.jobId = favourite_jobs.jobId
            LEFT JOIN job_applications ON jobs.jobId = job_applications.jobId
            WHERE jobs.userId=?
            GROUP BY jobs.jobId
            LIMIT ?
            ORDER BY jobs.updatedAt DESC
            ");
            $findJobsStmt->bind_param("si", $agentUserId, $limit);
        } else {
            $findJobsStmt = $db->getConnection()->prepare("
            SELECT jobs.*, COUNT(favourite_jobs.userId) AS favourite_job_count, COUNT(CASE WHEN job_applications.status = 'PENDING' THEN job_applications.userId END) AS job_application_count FROM jobs 
            LEFT JOIN favourite_jobs ON jobs.jobId = favourite_jobs.jobId
            LEFT JOIN job_applications ON jobs.jobId = job_applications.jobId
            WHERE jobs.userId=?
            GROUP BY jobs.jobId
            ORDER BY jobs.updatedAt DESC
            ");
            $findJobsStmt->bind_param("s", $agentUserId);
        }
        $findJobsStmt->execute();
        $result = $findJobsStmt->get_result();
        $findJobsStmt->close();
        $jobs = $result->fetch_all(MYSQLI_ASSOC); // Fetches all rows as an associative array
        echo json_encode(array("data" => $jobs));
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
