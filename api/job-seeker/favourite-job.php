<?php
require_once __DIR__ . "/../../schema/favourite-job.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";


$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required", "type" => "Error"));
    exit();
}
$result = Validation::validateSchema($_POST, $favouriteJobSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result, "type" => "Error"));
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
        // check if user exists
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT COUNT(*) from users WHERE userId=? AND role='Job Seeker'");
        $findExistingUserStmt->bind_param("s", $userId);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($userCount);
        $findExistingUserStmt->fetch();
        $findExistingUserStmt->close();
        if ($userCount === 0) {
            http_response_code(404);
            echo json_encode(array("message" => "User not found", "type" => "Error"));
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
            exit();
        }

        // check if job is already a favourite
        $findExistingFavouriteStmt = $db->getConnection()->prepare("SELECT COUNT(*) FROM favourite_jobs WHERE jobId=? AND userId=?");
        $findExistingFavouriteStmt->bind_param("ss", $jobId, $userId);
        $findExistingFavouriteStmt->execute();
        $findExistingFavouriteStmt->bind_result($favouriteCount);
        $findExistingFavouriteStmt->fetch();
        $findExistingFavouriteStmt->close();

        // remove favourite if already exists
        if ($favouriteCount > 0) {
            $removeFavouriteStmt = $db->getConnection()->prepare("DELETE FROM favourite_jobs WHERE jobId=? AND userId=?");
            $removeFavouriteStmt->bind_param("ss", $jobId, $userId);
            $removeFavouriteStmt->execute();
            $removeFavouriteStmt->close();
            echo json_encode(array("message" => "Job removed from favourites", "type" => "Success"));
            exit();
        }

        // create favourite
        $createFavouriteStmt = $db->getConnection()->prepare("INSERT INTO favourite_jobs (userId, jobId) VALUES (?,?)");
        $createFavouriteStmt->bind_param("ss", $userId, $jobId);
        $createFavouriteStmt->execute();
        $createFavouriteStmt->close();
        echo json_encode(array("message" => "Job added to favourites", "type" => "Success"));
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
