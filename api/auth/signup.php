<?php
$allowedRoles = ["JobSeeker", "Agent", "Admin"];
if (isset($_POST["fullName"]) && isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["phoneNumber"]) && isset($_POST["role"])) {
    $fullName = $_POST["fullName"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $phoneNumber = $_POST["phoneNumber"];
    $role = $_POST["role"];

    // check if the role is in ENUM
    if (!in_array($role, $allowedRoles, true)) {
        echo json_encode(array("message" => "Role does not exist", "type" => "Error"));
        exit();
    }

    require_once __DIR__ . "/../../lib/db.php";
    $db = DB_CONNECTION::getInstance();

    if ($db->getConnection()) {
        try {
            $findExistingUserStmt = $db->getConnection()->prepare("SELECT COUNT(*) from users WHERE email=?");
            $findExistingUserStmt->bind_param("s", $email);
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

            $createNewUserStmt = $db->getConnection()->prepare("INSERT INTO users (fullName, email, password, phoneNumber, role) VALUES (?,?,?,?,?)");
            $createNewUserStmt->bind_param("sssis", $fullName, $email, $password, $phoneNumber, $role);
            $createNewUserStmt->execute();
            $createNewUserStmt->close();
            echo json_encode(array("message" => "User successfully created", "type" => "Success"));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("message" => $e->getMessage(), "type" => "Error"));
        }
        $db->close();
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to connect to database", "type" => "Error"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Please fill in the required fields", "type" => "Error"));
}
