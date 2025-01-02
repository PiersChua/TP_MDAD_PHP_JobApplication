<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/agency.php";
require_once __DIR__ . "/../../utils/stringUtils.php";
require_once __DIR__ . "/../../utils/userValidator.php";

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

[$userId, $agencyAdminUserId, $name, $email, $phoneNumber, $address] = [
    $_POST["userId"],
    $_POST["agencyAdminUserId"],
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
        UserValidator::verifyIfUserExists($userId, $db->getConnection());
        $findDuplicateStmt = $db->getConnection()->prepare("
           SELECT 
            (SELECT COUNT(*) FROM users WHERE email = ?)
            + (SELECT COUNT(*) FROM agencies WHERE email = ? AND userId!=?),
            (SELECT COUNT(*) FROM users WHERE phoneNumber = ?)
            + (SELECT COUNT(*) FROM agencies WHERE phoneNumber = ? AND userId!=?)
        ");
        $findDuplicateStmt->bind_param("ssssss", $email, $email, $agencyAdminUserId, $phoneNumber, $phoneNumber, $agencyAdminUserId);
        $findDuplicateStmt->execute();
        $findDuplicateStmt->bind_result($emailCount, $phoneNumberCount);
        $findDuplicateStmt->fetch();
        $findDuplicateStmt->close();
        if ($emailCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Email is already in use"));
            exit();
        }
        if ($phoneNumberCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Phone Number is already in use"));
            exit();
        }

        $updateUserStmt = $db->getConnection()->prepare("UPDATE agencies SET name=?,email=?,phoneNumber=?,address=? WHERE userId=?");
        $updateUserStmt->bind_param("sssss", $name, $email, $phoneNumber, $address, $agencyAdminUserId);
        $updateUserStmt->execute();
        $updateUserStmt->close();
        echo json_encode(array("message" => "Agency updated"));
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
