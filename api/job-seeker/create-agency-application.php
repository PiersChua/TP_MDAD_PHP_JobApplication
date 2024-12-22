<?php
require_once __DIR__ . "/../../schema/agency-application.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/stringUtils.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);

if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}

// Validate schema
$result = Validation::validateSchema($_POST, $agencyApplicationSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}

[$name, $email, $phoneNumber, $address, $userId] = [
    $_POST["name"],
    StringUtils::lowercaseEmail($_POST["email"]),
    $_POST["phoneNumber"],
    !is_null(trim($_POST["address"])) ? trim($_POST["address"]) : null,
    $_POST["userId"]
];
if (!Validation::validateName($name)) {
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

// Verify token
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);


$db = Db::getInstance();
if ($db->getConnection()) {
    try {
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

        // check the db if a record already exists, combine the result with UNION ALL
        $findDuplicateStmt = $db->getConnection()->prepare("
            SELECT COUNT(*) 
            FROM (
                SELECT email FROM users WHERE email = ? OR phoneNumber = ?
                UNION ALL
                SELECT email FROM agencies WHERE email = ? OR phoneNumber = ? OR name = ?
                UNION ALL
                SELECT email FROM agency_applications WHERE email = ? OR phoneNumber = ? OR name = ?
            ) as combined
        ");
        $findDuplicateStmt->bind_param("ssssssss", $email, $phoneNumber, $email, $phoneNumber, $name, $email, $phoneNumber, $name);
        $findDuplicateStmt->execute();
        $findDuplicateStmt->bind_result($duplicateCount);
        $findDuplicateStmt->fetch();
        $findDuplicateStmt->close();

        if ($duplicateCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Email, phone number or agency name is already in use"));
            exit();
        }

        $createAgencyApplicationStmt = $db->getConnection()->prepare("INSERT INTO agency_applications (name, email, phoneNumber, address, userId) VALUES(?,?,?,?,?) ");
        $createAgencyApplicationStmt->bind_param("sssss", $name, $email, $phoneNumber, $address, $userId);
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
