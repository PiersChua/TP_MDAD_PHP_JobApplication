<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/stringUtils.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);

if (isset($_POST["userId"]) && !is_null($token)) {
    $result = Validation::validateSchema($_POST, $updateUserSchema);
    if ($result !== null) {
        http_response_code(400);
        echo json_encode(array("message" => $result, "type" => "Error"));
        exit();
    }
    [$userId, $fullName, $email, $phoneNumber, $token] = [$_POST["userId"], StringUtils::capitalizeName($_POST["fullName"]), $_POST["email"], $_POST["phoneNumber"], $token];
    /**
     *  Verify token
     */
    $payload = Jwt::decode($token);
    Jwt::verifyPayloadWithUserId($payload, $userId);
    $db = Db::getInstance();
    if ($db->getConnection()) {
        try {
            $findExistingUserStmt = $db->getConnection()->prepare("SELECT COUNT(*) from users WHERE (email=? OR phoneNumber=?) AND userId!=?");
            $findExistingUserStmt->bind_param("sss", $email, $phoneNumber, $userId);
            $findExistingUserStmt->execute();
            $findExistingUserStmt->bind_result($count);
            $findExistingUserStmt->fetch();
            $findExistingUserStmt->close();

            // check for existing user
            if ($count > 0) {
                http_response_code(400);
                echo json_encode(array("message" => "User already exists", "type" => "Error"));
                $db->close();
                exit();
            }

            $updateUserStmt = $db->getConnection()->prepare("UPDATE users SET fullName=?,email=?,phoneNumber=? WHERE userId=?");
            $updateUserStmt->bind_param("ssss", $fullName, $email, $phoneNumber, $userId);
            $updateUserStmt->execute();
            $updateUserStmt->close();

            http_response_code(200);
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
} else {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required", "type" => "Error"));
}
