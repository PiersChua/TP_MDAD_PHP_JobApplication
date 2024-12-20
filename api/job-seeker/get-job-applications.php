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
$userId = $_GET["userId"];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $findFavouritesStmt = $db->getConnection()->prepare("
        SELECT jobs.*, job_applications.status, job_applications.createdAt as job_application_created_at, job_applications.updatedAt as job_application_updated_at, agencies.name as agency_name FROM jobs
        INNER JOIN job_applications ON jobs.jobId = job_applications.jobId
        INNER JOIN users ON jobs.userId = users.userId
        INNER JOIN agencies ON users.agencyId = agencies.agencyId
        WHERE job_applications.userId=?");
        $findFavouritesStmt->bind_param("s", $userId);
        $findFavouritesStmt->execute();
        $result = $findFavouritesStmt->get_result();
        $findFavouritesStmt->close();
        $favouriteJobs = $result->fetch_all(MYSQLI_ASSOC); // Fetches all rows as an associative array
        echo json_encode(array("data" => $favouriteJobs, "type" => "Success"));
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
