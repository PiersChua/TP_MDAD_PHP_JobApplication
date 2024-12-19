<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required", "type" => "Error"));
    exit();
}
$userId = $_POST["userId"];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT fullName,email,dateOfBirth,phoneNumber,race,nationality,gender from users WHERE userId=?");
        $findExistingUserStmt->bind_param("s", $userId);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($fullName, $email, $dateOfBirth, $phoneNumber, $race, $nationality, $gender);

        // fetch returns true if user exist 
        if ($findExistingUserStmt->fetch()) {
            $userDetails = array(
                "fullName" => $fullName,
                "email" => $email,
                "dateOfBirth" => $dateOfBirth,
                "phoneNumber" => $phoneNumber,
                "race" => $race,
                "nationality" => $nationality,
                "gender" => $gender,
                "type" => "Success"
            );

            echo json_encode($userDetails);
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "User not found", "type" => "Error"));
        }
        $findExistingUserStmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage(), "type" => "Error"));
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to connect to database", "type" => "Error"));
    $db->close();
}
