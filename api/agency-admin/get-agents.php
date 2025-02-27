<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../utils/userValidator.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_GET["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_GET, $getAgentsSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $agencyAdminUserId] = [$_GET["userId"], $_GET["agencyAdminUserId"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());
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

        $findAgentsStmt = $db->getConnection()->prepare("
        SELECT users.userId,users.fullName, users.email, users.phoneNumber, COUNT(jobs.jobId) AS job_count from users
        LEFT JOIN jobs on users.userId=jobs.userId
        WHERE role='Agent' AND users.agencyId=?
        GROUP BY users.userId
        ORDER BY users.createdAt DESC
        ");
        $findAgentsStmt->bind_param("s", $agencyId);
        $findAgentsStmt->execute();
        $result = $findAgentsStmt->get_result();
        $findAgentsStmt->close();
        $users = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode(array("data" => $users));
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
