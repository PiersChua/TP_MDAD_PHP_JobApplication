<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/validation.php";
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
        UserValidator::verifyIfUserExists($userId, $db->getConnection());
        $findAgencyApplicationsStmt = $db->getConnection()->prepare("
        SELECT agencyApplicationId, name, email, phoneNumber, address, status, updatedAt from agency_applications
        ");
        $findAgencyApplicationsStmt->execute();
        $result = $findAgencyApplicationsStmt->get_result();
        $findAgencyApplicationsStmt->close();
        $agencies = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(array("data" => $agencies));
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
