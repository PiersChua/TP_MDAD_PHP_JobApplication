<?php
require_once __DIR__ . "/../../schema/job.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}

$result = Validation::validateSchema($_POST, $deleteJobSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $jobId] = [
    $_POST["userId"],
    $_POST["jobId"]
];
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = DB::getInstance();
if ($db->getConnection()) {
    try {
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT COUNT(*) from users WHERE userId=? AND role='Agent'");
        $findExistingUserStmt->bind_param("s", $userId);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($userCount);
        $findExistingUserStmt->fetch();
        $findExistingUserStmt->close();
        // check if agent exists
        if ($userCount === 0) {
            http_response_code(404);
            echo json_encode(array("message" => "Agent not found"));
            exit();
        }

        $deleteJobStmt = $db->getConnection()->prepare("DELETE FROM jobs WHERE jobId=? AND userId=?");
        $deleteJobStmt->bind_param("ss", $jobId, $userId);
        $deleteJobStmt->execute();
        $deleteJobStmt->close();
        echo json_encode(array("message" => "Job deleted"));
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
