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
$result = Validation::validateSchema($_POST, $promoteJobSeekerRoleSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $email, $agencyAdminUserId] = [$_POST["userId"], StringUtils::lowercaseEmail($_POST["email"]), $_POST["agencyAdminUserId"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $findExistingAgencyAdminStmt = $db->getConnection()->prepare("SELECT agencyId from agencies WHERE userId=?");
        $findExistingAgencyAdminStmt->bind_param("s", $agencyAdminUserId);
        $findExistingAgencyAdminStmt->execute();
        $findExistingAgencyAdminStmt->bind_result($agencyId);
        $findExistingAgencyAdminStmt->fetch();
        $findExistingAgencyAdminStmt->close();
        if ($agencyId === null) {
            http_response_code(404);
            echo json_encode(array("message" => "Agency Admin not found"));
            exit();
        }

        $promoteUserStmt = $db->getConnection()->prepare("
        UPDATE users
        SET role='Agent', agencyId=?
        WHERE users.email=? AND users.role='Job Seeker'");
        $promoteUserStmt->bind_param("ss", $agencyId, $email);
        $promoteUserStmt->execute();
        $promoteUserStmt->close();
        echo json_encode(array("message" => "Agent added"));
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
