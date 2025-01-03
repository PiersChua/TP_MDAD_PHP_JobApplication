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
if (!in_array($gender, $allowedGenders, true)) {
    http_response_code(400);
    echo json_encode(array("message" => "Gender does not exist"));
    exit();
}
if (!in_array($race, $allowedRaces, true)) {
    http_response_code(400);
    echo json_encode(array("message" => "Race does not exist"));
    exit();
}
if (!in_array($nationality, $allowedNationalities, true)) {
    http_response_code(400);
    echo json_encode(array("message" => "Nationality does not exist"));
    exit();
}
if (!Validation::validateUserName($fullName)) {
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
           SELECT 
            (SELECT COUNT(*) FROM users WHERE email = ?)
            + (SELECT COUNT(*) FROM agencies WHERE email = ?),
            (SELECT COUNT(*) FROM users WHERE phoneNumber = ?)
            + (SELECT COUNT(*) FROM agencies WHERE phoneNumber = ?)
        ");
        $findDuplicateStmt->bind_param("ssss", $email, $email, $phoneNumber, $phoneNumber);
        $findDuplicateStmt->execute();
        $findDuplicateStmt->bind_result($emailCount, $phoneNumberCount);
        $findDuplicateStmt->fetch();
        $findDuplicateStmt->close();
        if ($emailCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Email is already in use"));
            exit();
        }
        if ($phoneNumberCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Phone Number is already in use"));
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
