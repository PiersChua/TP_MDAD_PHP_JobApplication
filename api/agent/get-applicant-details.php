<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_GET["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
if (!isset($_GET["jobApplicationUserId"])) {
    http_response_code(400);
    echo json_encode(array("message" => "JobId is required"));
    exit();
}
[$userId, $jobApplicationUserId] = [$_GET["userId"], $_GET["jobApplicationUserId"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT fullName,email,dateOfBirth,phoneNumber,race,nationality,gender from users WHERE userId=?");
        $findExistingUserStmt->bind_param("s", $jobApplicationUserId);
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
            );

            echo json_encode($userDetails);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "User not found"));
        }
        $findExistingUserStmt->close();
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
