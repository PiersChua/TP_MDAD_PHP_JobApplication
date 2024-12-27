<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/agency.php";
require_once __DIR__ . "/../../utils/stringUtils.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_POST, $updateAgencySchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}

[$userId, $name, $email, $phoneNumber, $address] = [
    $_POST["userId"],
    $_POST["name"],
    StringUtils::lowercaseEmail($_POST["email"]),
    $_POST["phoneNumber"],
    $_POST["address"]

];
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
                SELECT email FROM users WHERE email = ? OR phoneNumber = ?
                UNION ALL
                SELECT email FROM agencies WHERE (email = ? OR phoneNumber = ?) AND agencies.userId != ?
            ) as combined
        ");
        $findDuplicateStmt->bind_param("sssss", $email, $phoneNumber, $email, $phoneNumber, $userId);
        $findDuplicateStmt->execute();
        $findDuplicateStmt->bind_result($duplicateCount);
        $findDuplicateStmt->fetch();
        $findDuplicateStmt->close();
        if ($duplicateCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Email or phone number is already in use"));
            exit();
        }

        $updateUserStmt = $db->getConnection()->prepare("UPDATE agencies SET name=?,email=?,phoneNumber=?,address=? WHERE userId=?");
        $updateUserStmt->bind_param("sssss", $name, $email, $phoneNumber, $address, $userId);
        $updateUserStmt->execute();
        $updateUserStmt->close();
        echo json_encode(array("message" => "Profile updated"));
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
