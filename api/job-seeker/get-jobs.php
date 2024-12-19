<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required", "type" => "Error"));
    exit();
}
$userId = $_POST["userId"];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $findJobsStmt = $db->getConnection()->prepare("SELECT * FROM jobs");
        $findJobsStmt->execute();
        $result = $findJobsStmt->get_result();
        $findJobsStmt->close();
        $jobs = $result->fetch_all(MYSQLI_ASSOC); // Fetches all rows as an associative array
        echo json_encode(array("data" => $jobs, "type" => "Success"));
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
