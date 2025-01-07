<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/stringUtils.php";
require_once __DIR__ . "/../../utils/userValidator.php";


$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_GET["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_GET, $getJobSeekerDetailsSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $email] = [$_GET["userId"], StringUtils::lowercaseEmail($_GET["email"])];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());
        $findExistingUserStmt = $db->getConnection()->prepare("
        SELECT users.userId, users.fullName, users.email from users
        WHERE users.email=? AND users.role='Job Seeker'");
        $findExistingUserStmt->bind_param("s", $email);
        $findExistingUserStmt->execute();
        $result = $findExistingUserStmt->get_result();
        $findExistingUserStmt->close();
        $user = $result->fetch_assoc();
        if ($user === null) {
            http_response_code(404);
            echo json_encode(array("message" => "User not found.\n Please ensure that the user is a job seeker."));
            exit();
        }
        echo json_encode($user);
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
