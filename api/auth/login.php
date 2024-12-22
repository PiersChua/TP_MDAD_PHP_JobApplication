<?php
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/stringUtils.php";


$result = Validation::validateSchema($_POST, $loginSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$email, $password] = [StringUtils::lowercaseEmail($_POST["email"]), $_POST["password"]];
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT userId,password,role, fullName from users WHERE email=?");
        $findExistingUserStmt->bind_param("s", $email);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($userId, $userPassword, $role, $fullName);

        // fetch returns true if user exist 
        if ($findExistingUserStmt->fetch()) {
            $passwordMatched = password_verify($password, $userPassword);
            if ($passwordMatched) {
                $token = Jwt::encode(array("userId" => $userId, "role" => $role));
                echo json_encode(array("message" => "Login Successful", "token" => $token, "userId" => $userId, "role" => $role, "fullName" => $fullName));
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Invalid Credentials"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Invalid Credentials"));
        }
        $findExistingUserStmt->close();
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
