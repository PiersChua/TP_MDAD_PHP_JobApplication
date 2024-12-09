<?php
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";

$result = Validation::validateSchema($_POST, $loginSchema);
if ($result === null) {
    [$email, $password] = [$_POST["email"], $_POST["password"]];
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
                    echo json_encode(array("message" => "Login Successful", "type" => "Success", "token" => $token, "userId" => $userId, "role" => $role, "fullName" => $fullName));
                    /**
                     *  Verify token
                     */
                    // $decodedToken = Jwt::decode($token);
                    // if (isset($decodedToken["type"]) && $decodedToken["type"] === "Error") {
                    //     http_response_code(401);
                    //     echo json_encode($decodedToken);
                    // } else {
                    //     echo json_encode($decodedToken);
                    // }
                } else {
                    http_response_code(401);
                    echo json_encode(array("message" => "Invalid Credentials", "type" => "Error"));
                }
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Invalid Credentials", "type" => "Error"));
            }
            $findExistingUserStmt->close();
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("message" => $e->getMessage(), "type" => "Error"));
        }
        $db->close();
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to connect to database", "type" => "Error"));
        $db->close();
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => $result, "type" => "Error"));
}
