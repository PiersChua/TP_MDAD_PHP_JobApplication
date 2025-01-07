<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/agency-application.php";
require_once __DIR__ . "/../../utils/userValidator.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_GET["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_GET, $getApplicantsSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $agencyApplicationId] = [$_GET["userId"], $_GET["agencyApplicationId"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());
        $findExistingApplicationStmt = $db->getConnection()->prepare("
        SELECT agency_applications.name,agency_applications.email,agency_applications.phoneNumber,agency_applications.address,agency_applications.image,
        users.fullName as user_fullName, users.email as user_email, users.phoneNumber as user_phoneNumber, users.dateOfBirth as user_dateOfBirth, users.race as user_race, users.nationality as user_nationality, users.gender as user_gender, users.image as user_image
        from agency_applications
        LEFT JOIN users on users.userId=agency_applications.userId
        WHERE agency_applications.agencyApplicationId=?
        ");
        $findExistingApplicationStmt->bind_param("s", $agencyApplicationId);
        $findExistingApplicationStmt->execute();
        $result = $findExistingApplicationStmt->get_result();
        $findExistingApplicationStmt->close();
        $applicant = $result->fetch_assoc();
        if ($applicant === null) {
            http_response_code(404);
            echo json_encode(array("message" => "User not found"));
            exit();
        }
        if (!is_null($applicant["image"])) {
            $applicant["image"] = base64_encode($applicant["image"]);
        }
        if (!is_null($applicant["user_image"])) {
            $applicant["user_image"] = base64_encode($applicant["user_image"]);
        }
        echo json_encode($applicant);
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
