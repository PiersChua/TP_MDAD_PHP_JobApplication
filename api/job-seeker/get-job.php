<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/userValidator.php";

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
        UserValidator::verifyIfUserExists($userId, $db->getConnection());
        /**
         * First inner join links jobs to user who created them
         * Second inner join links users to their respective agencies
         * Where clause filters the jobs where the jobId matches
         */
        $findExistingJobStmt = $db->getConnection()->prepare("
            SELECT jobs.*, 
            users.fullName as user_fullName, users.email as user_email, users.phoneNumber as user_phoneNumber, users.image as user_image,
            agencies.name as agency_name, agencies.email as agency_email, agencies.phoneNumber as agency_phoneNumber, agencies.address as agency_address, agencies.image as agency_image
            FROM jobs
            INNER JOIN users ON jobs.userId = users.userId
            INNER JOIN agencies ON users.agencyId = agencies.agencyId
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
        if (!is_null($job['user_image'])) {
            $job['user_image'] = base64_encode($job['user_image']);
        }
        if (!is_null($job['agency_image'])) {
            $job['agency_image'] = base64_encode($job['agency_image']);
        }
        $findExistingFavouriteStmt = $db->getConnection()->prepare("
            SELECT COUNT(*) FROM favourite_jobs WHERE userId = ? AND jobId = ?
        ");
        $findExistingFavouriteStmt->bind_param("ss", $userId, $jobId);
        $findExistingFavouriteStmt->execute();
        $findExistingFavouriteStmt->bind_result($favouriteCount);
        $findExistingFavouriteStmt->fetch();
        $findExistingFavouriteStmt->close();

        $findExistingJobApplicationStmt = $db->getConnection()->prepare("
            SELECT COUNT(*) FROM job_applications WHERE userId = ? AND jobId = ?
        ");
        $findExistingJobApplicationStmt->bind_param("ss", $userId, $jobId);
        $findExistingJobApplicationStmt->execute();
        $findExistingJobApplicationStmt->bind_result($applicationCount);
        $findExistingJobApplicationStmt->fetch();
        $findExistingJobApplicationStmt->close();
        $job['isFavourite'] = $favouriteCount > 0;
        $job["isApplied"] = $applicationCount > 0;
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
