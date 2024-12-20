<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_GET["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required", "type" => "Error"));
    exit();
}
if (!isset($_GET["jobId"])) {
    http_response_code(400);
    echo json_encode(array("message" => "JobId is required", "type" => "Error"));
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
        /**
         * First inner join links jobs to user who created them
         * Second inner join links users to their respective agencies
         * Where clause filters the jobs where the jobId matches
         */
        // $findExistingJobStmt = $db->getConnection()->prepare("
        //     SELECT jobs.*, agencies.name
        //     FROM jobs
        //     INNER JOIN users ON jobs.userId = users.userId
        //     INNER JOIN agencies ON users.agencyId = agencies.agencyId
        //     WHERE jobs.jobId = ?
        // ");
        $findExistingJobStmt = $db->getConnection()->prepare("
            SELECT jobs.*
            FROM jobs
            WHERE jobs.jobId = ?
        ");
        $findExistingJobStmt->bind_param("s", $jobId);
        $findExistingJobStmt->execute();
        $result = $findExistingJobStmt->get_result();
        $findExistingJobStmt->close();
        $job = $result->fetch_assoc(); // fetches a single row
        if ($job !== null) {
            $findExistingFavouriteStmt = $db->getConnection()->prepare("
            SELECT COUNT(*) FROM favourite_jobs WHERE userId = ? AND jobId = ?
        ");
            $findExistingFavouriteStmt->bind_param("ss", $userId, $jobId);
            $findExistingFavouriteStmt->execute();
            $findExistingFavouriteStmt->bind_result($favouriteCount);
            $findExistingFavouriteStmt->fetch();
            $findExistingFavouriteStmt->close();
            $job['isFavourite'] = $favouriteCount > 0;
            echo json_encode(array("data" => $job, "type" => "Success"));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Job not found", "type" => "Error"));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage(), "type" => "Error"));
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to connect to database", "type" => "Error"));
    $db->close();
}
