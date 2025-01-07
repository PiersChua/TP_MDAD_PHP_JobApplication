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
        if (isset($_GET["query"]) && !empty($_GET["query"])) {
            $searchQuery = "%" . strtolower($_GET["query"]) . "%";
            $findJobsStmt = $db->getConnection()->prepare("
            SELECT jobs.*, agencies.name as agency_name 
            FROM jobs
            INNER JOIN users ON jobs.userId = users.userId
            INNER JOIN agencies ON users.agencyId = agencies.agencyId
            WHERE LOWER(jobs.position) LIKE ?
            ORDER BY jobs.updatedAt DESC
            ");
            $findJobsStmt->bind_param("s", $searchQuery);
        } else {
            $findJobsStmt = $db->getConnection()->prepare("
            SELECT jobs.*, agencies.name as agency_name FROM jobs
            INNER JOIN users ON jobs.userId = users.userId
            INNER JOIN agencies ON users.agencyId = agencies.agencyId
            ORDER BY jobs.updatedAt DESC
            ");
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
