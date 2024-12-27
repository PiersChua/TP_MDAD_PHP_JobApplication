<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/stringUtils.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_POST, $updateUserSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}

[$userId, $userIdToBeUpdated, $fullName, $email, $phoneNumber, $dateOfBirth, $gender, $race, $nationality] = [
    $_POST["userId"],
    $_POST["userIdToBeUpdated"],
    StringUtils::capitalizeName($_POST["fullName"]),
    StringUtils::lowercaseEmail($_POST["email"]),
    $_POST["phoneNumber"],
    $_POST["dateOfBirth"],
    $_POST["gender"],
    $_POST["race"],
    $_POST["nationality"],

];
if (!in_array($gender, $allowedGender, true)) {
    http_response_code(400);
    echo json_encode(array("message" => "Gender does not exist"));
    exit();
}
if (!in_array($race, $allowedRace, true)) {
    http_response_code(400);
    echo json_encode(array("message" => "Race does not exist"));
    exit();
}
if (!in_array($nationality, $allowedNationality, true)) {
    http_response_code(400);
    echo json_encode(array("message" => "Nationality does not exist"));
    exit();
}
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $findDuplicateStmt = $db->getConnection()->prepare("
           SELECT 
            (SELECT COUNT(*) FROM users WHERE email = ? AND userId!=?)
            + (SELECT COUNT(*) FROM agencies WHERE email = ?) ,
            (SELECT COUNT(*) FROM users WHERE phoneNumber = ? AND userId!=?)
            + (SELECT COUNT(*) FROM agencies WHERE phoneNumber = ?)
        ");
        $findDuplicateStmt->bind_param("ssssss", $email, $userIdToBeUpdated, $email, $phoneNumber, $userIdToBeUpdated, $phoneNumber);
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

        $updateUserStmt = $db->getConnection()->prepare("UPDATE users SET fullName=?,email=?,phoneNumber=?,dateOfBirth=?,gender=?,race=?,nationality=? WHERE userId=?");
        $updateUserStmt->bind_param("ssssssss", $fullName, $email, $phoneNumber, $dateOfBirth, $gender, $race, $nationality, $userIdToBeUpdated);
        $updateUserStmt->execute();
        $updateUserStmt->close();
        echo json_encode(array("message" => "Profile updated"));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage()));
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to connect to database"));
    $db->close();
}
