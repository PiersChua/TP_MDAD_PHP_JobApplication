<?php
require_once __DIR__ . "/../../schema/favourite-job.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/userValidator.php";


$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_POST, $favouriteJobSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
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
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());

        // check if job exists
        $findExistingJobStmt = $db->getConnection()->prepare("SELECT COUNT(*) FROM jobs WHERE jobId=?");
        $findExistingJobStmt->bind_param("s", $jobId);
        $findExistingJobStmt->execute();
        $findExistingJobStmt->bind_result($jobCount);
        $findExistingJobStmt->fetch();
        $findExistingJobStmt->close();
        if ($jobCount === 0) {
            http_response_code(404);
            echo json_encode(array("message" => "Job not found"));
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
            echo json_encode(array("message" => "Job removed from favourites"));
            exit();
        }

        // create favourite
        $createFavouriteStmt = $db->getConnection()->prepare("INSERT INTO favourite_jobs (userId, jobId) VALUES (?,?)");
        $createFavouriteStmt->bind_param("ss", $userId, $jobId);
        $createFavouriteStmt->execute();
        $createFavouriteStmt->close();
        echo json_encode(array("message" => "Job added to favourites"));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage()));
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to connect to the database"));
    $db->close();
}
