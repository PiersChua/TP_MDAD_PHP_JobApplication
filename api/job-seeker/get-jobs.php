<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";

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
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            // extract the jobs based on the limit given
            $limit = $_GET["limit"];
            $findJobsStmt = $db->getConnection()->prepare("
            SELECT jobs.*, agencies.name as agency_name FROM jobs
            INNER JOIN users ON jobs.userId = users.userId
            INNER JOIN agencies ON users.agencyId = agencies.agencyId
            LIMIT ?");
            $findJobsStmt->bind_param("i", $limit);
        } else {
            $findJobsStmt = $db->getConnection()->prepare("
            SELECT jobs.*, agencies.name as agency_name FROM jobs
            INNER JOIN users ON jobs.userId = users.userId
            INNER JOIN agencies ON users.agencyId = agencies.agencyId
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
