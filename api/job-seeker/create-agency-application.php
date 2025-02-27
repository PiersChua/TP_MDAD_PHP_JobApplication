<?php
require_once __DIR__ . "/../../schema/agency-application.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/stringUtils.php";
require_once __DIR__ . "/../../utils/userValidator.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);

if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token are required"));
    exit();
}

// Validate schema
$result = Validation::validateSchema($_POST, $createAgencyApplicationSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}

[$name, $email, $phoneNumber, $address, $image, $userId] = [
    $_POST["name"],
    StringUtils::lowercaseEmail($_POST["email"]),
    $_POST["phoneNumber"],
    trim($_POST["address"]),
    isset($_POST["image"]) ? base64_decode($_POST["image"]) : null,
    $_POST["userId"]
];

if (!Validation::validateUserName($name)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid name, please type in the correct format"));
    exit();
}
if (!Validation::validateEmail($email)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid email, please type in the correct format"));
    exit();
}
if (!Validation::validatePhoneNumber($phoneNumber)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid phone number, please type in the correct format"));
    exit();
}
if (!Validation::validateAgencyAddress($address)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid address, please type in the correct format"));
    exit();
}

// Verify token
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);


$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT COUNT(*) from users WHERE userId=? AND role='Job Seeker'");
        $findExistingUserStmt->bind_param("s", $userId);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($userCount);
        $findExistingUserStmt->fetch();
        $findExistingUserStmt->close();

        if ($userCount === 0) {
            http_response_code(404);
            echo json_encode(array("message" => "User not found"));
            exit();
        }

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
        $createAgencyApplicationStmt = $db->getConnection()->prepare("
            INSERT INTO agency_applications (name, email, phoneNumber, address, userId, image) 
            VALUES (?, ?, ?, ?, ?, ?)
            ");
        $createAgencyApplicationStmt->bind_param("sssssb", $name, $email, $phoneNumber, $address, $userId, $image);
        // sends the blob image in packets, 5 represents the index of the param
        if ($image !== null) {
            $createAgencyApplicationStmt->send_long_data(5, $image);
        }
        $createAgencyApplicationStmt->execute();
        $createAgencyApplicationStmt->close();
        echo json_encode(array("message" => "Agency application submitted"));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage()));
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to connect to the database"));
    $db->close();
}
