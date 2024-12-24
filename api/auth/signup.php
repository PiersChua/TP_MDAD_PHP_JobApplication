<?php

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
[$fullName, $email, $password, $phoneNumber, $role, $dateOfBirth, $gender, $race, $nationality] = [
    StringUtils::capitalizeName($_POST["fullName"]),
    StringUtils::lowercaseEmail($_POST["email"]),
    $_POST["password"],
    $_POST["phoneNumber"],
    $_POST["role"],
    $_POST["dateOfBirth"],
    $_POST["gender"],
    $_POST["race"],
    $_POST["nationality"],
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
        $findDuplicateStmt = $db->getConnection()->prepare("
            SELECT COUNT(*) 
            FROM (
                SELECT email FROM users WHERE email = ? OR phoneNumber = ?
                UNION ALL
                SELECT email FROM agencies WHERE email = ? OR phoneNumber = ?
            ) as combined
        ");
        $findDuplicateStmt->bind_param("sssss", $email, $phoneNumber, $email, $phoneNumber);
        $findDuplicateStmt->execute();
        $findDuplicateStmt->bind_result($duplicateCount);
        $findDuplicateStmt->fetch();
        $findDuplicateStmt->close();
        if ($duplicateCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Email or phone number is already in use"));
            exit();
        }

        $createUserStmt = $db->getConnection()->prepare("INSERT INTO users (fullName, email, password, phoneNumber, role, dateOfBirth, gender, race, nationality) VALUES (?,?,?,?,?,?,?,?,?)");
        $createUserStmt->bind_param("sssssssss", $fullName, $email, $hashedPassword, $phoneNumber, $role, $dateOfBirth, $gender, $race, $nationality);
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
