<?php
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/userValidator.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_POST, $changePasswordSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $password, $newPassword] = [$_POST["userId"], $_POST["password"], $_POST["newPassword"]];
$hashedNewPassword =
    password_hash($newPassword, PASSWORD_BCRYPT);
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT password from users WHERE userId=?");
        $findExistingUserStmt->bind_param("s", $userId);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($userPassword);
        // fetch returns true if user exist 
        if (!$findExistingUserStmt->fetch()) {
            http_response_code(400);
            echo json_encode(array("message" => "Credentials not found"));
            exit();
        }
        $findExistingUserStmt->close();
        $passwordMatched = password_verify($password, $userPassword);
        if (!$passwordMatched) {
            http_response_code(400);
            echo json_encode(array("message" => "Invalid Credentials"));
            exit();
        }
        $changeUserPasswordStmt = $db->getConnection()->prepare("UPDATE users SET password=? WHERE userId=?");
        $changeUserPasswordStmt->bind_param("ss", $hashedNewPassword, $userId);
        $changeUserPasswordStmt->execute();
        $changeUserPasswordStmt->close();
        echo json_encode(array("message" => "Password has been changed"));
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
