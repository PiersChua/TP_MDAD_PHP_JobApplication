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
        $findFavouritesStmt = $db->getConnection()->prepare("
        SELECT jobs.*, agencies.name as agency_name FROM jobs
        INNER JOIN favourite_jobs ON jobs.jobId = favourite_jobs.jobId
        INNER JOIN users ON jobs.userId = users.userId
        INNER JOIN agencies ON users.agencyId = agencies.agencyId
        WHERE favourite_jobs.userId=?
        ORDER BY favourite_jobs.createdAt DESC
        ");
        $findFavouritesStmt->bind_param("s", $userId);
        $findFavouritesStmt->execute();
        $result = $findFavouritesStmt->get_result();
        $findFavouritesStmt->close();
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
