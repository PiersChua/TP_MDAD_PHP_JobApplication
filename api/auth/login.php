<?php

if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    require_once __DIR__ . "/../../lib/db.php";
    $db = DB_CONNECTION::getInstance();
    if ($db->getConnection()) {
        try {
            $findExistingUserStmt = $db->getConnection()->prepare("SELECT userId,password,role from users WHERE email=?");
            $findExistingUserStmt->bind_param("s", $email);
            $findExistingUserStmt->execute();
            $findExistingUserStmt->bind_result($userId, $userPassword, $role);

            // fetch returns true if user exist 
            if ($findExistingUserStmt->fetch()) {
                $passwordMatched = password_verify($password, $userPassword);
                if ($passwordMatched) {
                    require_once __DIR__ . "/../../utils/JWT.php";
                    $token = JWT::encode(array("userId" => $userId, "role" => $role));
                    echo json_encode(array("message" => "Login Successful", "type" => "Success", "token" => $token));
                    /**
                     *  Verify token
                     */
                    // $decodedToken = JWT::decode($token);
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
    echo json_encode(array("message" => "Please fill in the required fields", "type" => "Error"));
}
