<?php
$allowedRoles = ["Job Seeker", "Admin"];
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/stringUtils.php";

$result = Validation::validateSchema($_POST, $signUpSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$fullName, $email, $password, $phoneNumber, $role] = [
    StringUtils::capitalizeName($_POST["fullName"]),
    StringUtils::lowercaseEmail($_POST["email"]),
    $_POST["password"],
    $_POST["phoneNumber"],
    $_POST["role"]
];
$hashedPassword =
    password_hash($_POST["password"], PASSWORD_BCRYPT);

// check if the role is in ENUM
if (!in_array($role, $allowedRoles, true)) {
    http_response_code(400);
    echo json_encode(array("message" => "Role does not exist"));
    exit();
}
if (!Validation::validateName($fullName)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid name, please type in the correct format"));
    exit();
}
if (!Validation::validateEmail($email)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid email type, please type in the correct format"));
    exit();
}
if (!Validation::validatePhoneNumber($phoneNumber)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid phone number, please type in the correct format"));
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
            echo json_encode(array("message" => "User already exists"));
            exit();
        }

        $createUserStmt = $db->getConnection()->prepare("INSERT INTO users (fullName, email, password, phoneNumber, role) VALUES (?,?,?,?,?)");
        $createUserStmt->bind_param("sssss", $fullName, $email, $hashedPassword, $phoneNumber, $role);
        $createUserStmt->execute();
        $createUserStmt->close();
        echo json_encode(array("message" => "User created"));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage()));
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to connect to database"));
}
