<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../utils/userValidator.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_GET["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$userId = $_GET["userId"];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());
        $getTop3FavouriteJobsStmt = $db->getConnection()->prepare("
        SELECT jobs.position, COUNT(favourite_jobs.jobId) as favourite_job_count FROM jobs
        INNER JOIN favourite_jobs ON jobs.jobId=favourite_jobs.jobId
        GROUP BY jobs.jobId
        ORDER BY favourite_job_count DESC
        LIMIT 3
        ");
        $getTop3FavouriteJobsStmt->execute();
        $favouriteJobResult = $getTop3FavouriteJobsStmt->get_result();
        $getTop3FavouriteJobsStmt->close();
        $favouriteJobProportion = $favouriteJobResult->fetch_all(MYSQLI_ASSOC);

        $getJobApplicationStatusProportiontStmt = $db->getConnection()->prepare("
        SELECT status, COUNT(*) as job_application_count FROM job_applications
        INNER JOIN jobs ON jobs.jobId=job_applications.jobId
        INNER JOIN users on jobs.userId=users.userId
        WHERE jobs.userId=?
        GROUP BY status
        LIMIT 3
        ");
        $getJobApplicationStatusProportiontStmt->bind_param("s", $userId);
        $getJobApplicationStatusProportiontStmt->execute();
        $jobApplicationResult = $getJobApplicationStatusProportiontStmt->get_result();
        $getJobApplicationStatusProportiontStmt->close();
        $jobApplicationProportion = $jobApplicationResult->fetch_all(MYSQLI_ASSOC);

        $getTop3AppliedJobsStmt = $db->getConnection()->prepare("
        SELECT jobs.position, COUNT(job_applications.jobId) as job_application_count FROM jobs
        INNER JOIN job_applications ON jobs.jobId=job_applications.jobId
        GROUP BY jobs.jobId
        ORDER BY job_application_count DESC
        LIMIT 3
        ");
        $getTop3AppliedJobsStmt->execute();
        $jobResult = $getTop3AppliedJobsStmt->get_result();
        $getTop3AppliedJobsStmt->close();
        $jobProportion = $jobResult->fetch_all(MYSQLI_ASSOC);

        $data = array("favourited_job_data" => $favouriteJobProportion, "job_application_data" => $jobApplicationProportion, "applied_job_data" => $jobProportion);
        echo json_encode($data);
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
