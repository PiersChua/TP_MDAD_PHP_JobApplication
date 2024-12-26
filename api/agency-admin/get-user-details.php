<?php
require_once __DIR__ . "/../../utils/validation.php";
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
        $findExistingUserStmt = $db->getConnection()->prepare("
        SELECT users.*,
        agencies.name as agency_name, agencies.email as agency_email, agencies.phoneNumber as agency_phone_number, agencies.address as agency_address
        from users 
        LEFT JOIN agencies ON users.userId=agencies.userId
        WHERE users.userId=?");
        $findExistingUserStmt->bind_param("s", $userId);
        $findExistingUserStmt->execute();
        $result = $findExistingUserStmt->get_result();
        $findExistingUserStmt->close();
        $user = $result->fetch_assoc();
        if ($user === null) {
            http_response_code(404);
            echo json_encode(array("message" => "User not found"));
            exit();
        }
        echo json_encode($user);
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
