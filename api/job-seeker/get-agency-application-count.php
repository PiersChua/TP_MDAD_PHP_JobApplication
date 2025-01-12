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
        $findExistingAgencyApplicationStmt = $db->getConnection()->prepare("
        SELECT COUNT(*) FROM agency_applications
        WHERE userId=? AND status='PENDING'
        ");
        $findExistingAgencyApplicationStmt->bind_param("s", $userId);
        $findExistingAgencyApplicationStmt->execute();
        $findExistingAgencyApplicationStmt->bind_result($agencyApplicationCount);
        $findExistingAgencyApplicationStmt->fetch();
        $findExistingAgencyApplicationStmt->close();
        echo json_encode(array("data" => $agencyApplicationCount));
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
