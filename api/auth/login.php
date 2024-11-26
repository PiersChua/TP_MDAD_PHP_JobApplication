<?php

if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    require_once __DIR__ . "/../../lib/db.php";
    $db = DB_CONNECTION::getInstance();
    if ($db->getConnection()) {
        try {
            $findExistingUserStmt = $db->getConnection()->prepare("SELECT email,password from users WHERE email=?");
            $findExistingUserStmt->bind_param("s", $email);
            $findExistingUserStmt->execute();
            $findExistingUserStmt->bind_result($userEmail, $userPassword);

            // fetch returns true if user exist 
            if ($findExistingUserStmt->fetch()) {
                $passwordMatched = password_verify($password, $userPassword);
                if ($passwordMatched) {
                    echo "Login Successful";
                } else {
                    echo "Incorrect password";
                }
            } else {
                echo "User not found";
            }
            $findExistingUserStmt->close();
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("message" => $e->getMessage(), "type" => "Error"));
        }
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to connect to database", "type" => "Error"));
        $db->close();
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Please fill in the required fields", "type" => "Error"));
}
