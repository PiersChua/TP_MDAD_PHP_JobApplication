<?php
$allowedRoles = ["Job Seeker", "Admin"];
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/stringUtils.php";

$result = Validation::validateSchema($_POST, $signUpSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result, "type" => "Error"));
    exit();
}
[$fullName, $email, $password, $phoneNumber, $role] = [
    StringUtils::capitalizeName($_POST["fullName"]),
    $_POST["email"],
    $_POST["password"],
    $_POST["phoneNumber"],
    $_POST["role"]
];
$hashedPassword =
    password_hash($_POST["password"], PASSWORD_BCRYPT);

// check if the role is in ENUM
if (!in_array($role, $allowedRoles, true)) {
    echo json_encode(array("message" => "Role does not exist", "type" => "Error"));
    exit();
}
if (!Validation::validateName($fullName)) {
    echo json_encode(array("message" => "Invalid name, please type in the correct format", "type" => "Error"));
    exit();
}
if (!Validation::validateEmail($email)) {
    echo json_encode(array("message" => "Invalid email type, please type in the correct format", "type" => "Error"));
    exit();
}
if (!Validation::validatePhoneNumber($phoneNumber)) {
    echo json_encode(array("message" => "Invalid phone number, please type in the correct format", "type" => "Error"));
    exit();
}

$db = Db::getInstance();

if ($db->getConnection()) {
    try {
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT COUNT(*) from users WHERE email=? OR phoneNumber=?");
        $findExistingUserStmt->bind_param("ss", $email, $phoneNumber);
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

        $createUserStmt = $db->getConnection()->prepare("INSERT INTO users (fullName, email, password, phoneNumber, role) VALUES (?,?,?,?,?)");
        $createUserStmt->bind_param("sssss", $fullName, $email, $hashedPassword, $phoneNumber, $role);
        $createUserStmt->execute();
        $createUserStmt->close();
        echo json_encode(array("message" => "User created", "type" => "Success"));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage(), "type" => "Error"));
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to connect to database", "type" => "Error"));
}
