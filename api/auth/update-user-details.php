<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/stringUtils.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required", "type" => "Error"));
    exit();
}
$result = Validation::validateSchema($_POST, $updateUserSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result, "type" => "Error"));
    exit();
}
[$userId, $fullName, $email, $phoneNumber] = [$_POST["userId"], StringUtils::capitalizeName($_POST["fullName"]), $_POST["email"], $_POST["phoneNumber"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $findDuplicateStmt = $db->getConnection()->prepare("
            SELECT COUNT(*) 
            FROM (
                SELECT email FROM users WHERE (email = ? OR phoneNumber = ?) AND userId != ?
                UNION ALL
                SELECT email FROM agencies WHERE email = ? OR phoneNumber = ?
            ) as combined
        ");
        $findDuplicateStmt->bind_param("sssss", $email, $phoneNumber, $userId, $email, $phoneNumber);
        $findDuplicateStmt->execute();
        $findDuplicateStmt->bind_result($duplicateCount);
        $findDuplicateStmt->fetch();
        $findDuplicateStmt->close();
        if ($duplicateCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Email or phone number is already in use", "type" => "Error"));
            exit();
        }

        $updateUserStmt = $db->getConnection()->prepare("UPDATE users SET fullName=?,email=?,phoneNumber=? WHERE userId=?");
        $updateUserStmt->bind_param("ssss", $fullName, $email, $phoneNumber, $userId);
        $updateUserStmt->execute();
        $updateUserStmt->close();
        echo json_encode(array("message" => "Profile updated", "type" => "Success"));
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
