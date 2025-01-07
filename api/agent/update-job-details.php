<?php
require_once __DIR__ . "/../../schema/job.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/userValidator.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}

$result = Validation::validateSchema($_POST, $updateJobSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
if (!isset($_POST["partTimeSalary"]) && !isset($_POST["fullTimeSalary"])) {
    http_response_code(400);
    echo json_encode(array("message" => "At least part-time or full-time salary is required"));
    exit();
}
[$position, $responsibilities, $description, $location, $schedule, $organisation, $partTimeSalary, $fullTimeSalary, $userId, $jobId, $agentUserId] = [
    $_POST["position"],
    $_POST["responsibilities"],
    $_POST["description"],
    $_POST["location"],
    $_POST["schedule"],
    $_POST["organisation"],
    $_POST["partTimeSalary"] ?? null,
    $_POST["fullTimeSalary"] ?? null,
    $_POST["userId"],
    $_POST["jobId"],
    $_POST["agentUserId"]
];
if (!Validation::validateJobPosition($position)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid position, please type in the correct format"));
    exit();
}
if (!Validation::validateJobResponsibilities($responsibilities)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid responsibilities, please type in the correct format"));
    exit();
}
if (!Validation::validateJobDescription($description)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid description, please type in the correct format"));
    exit();
}
if (!Validation::validateJobLocation($location)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid location, please type in the correct format"));
    exit();
}
if (!Validation::validateJobSchedule($schedule)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid schedule, please type in the correct format"));
    exit();
}
if (!Validation::validateJobOrganisation($organisation)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid organisation, please type in the correct format"));
    exit();
}
if (!Validation::validateJobSalary($partTimeSalary, $fullTimeSalary)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid salary, please type in the correct format"));
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
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT COUNT(*) from users WHERE userId=? AND role='Agent'");
        $findExistingUserStmt->bind_param("s", $agentUserId);
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

        $findExistingJobStmt = $db->getConnection()->prepare("SELECT COUNT(*) from jobs WHERE jobId=? AND userId=?");
        $findExistingJobStmt->bind_param("ss", $jobId, $agentUserId);
        $findExistingJobStmt->execute();
        $findExistingJobStmt->bind_result($userCount);
        $findExistingJobStmt->fetch();
        $findExistingJobStmt->close();
        // check if agent exists
        if ($userCount === 0) {
            http_response_code(404);
            echo json_encode(array("message" => "Agent not found"));
            exit();
        }

        $updateJobStmt = $db->getConnection()->prepare("UPDATE jobs
        SET position=?, responsibilities=?, description=?, location=?, schedule=?, organisation=?, partTimeSalary=?, fullTimeSalary=?
        WHERE jobId=?
        ");
        $updateJobStmt->bind_param("ssssssdds", $position, $responsibilities, $description, $location, $schedule, $organisation, $partTimeSalary, $fullTimeSalary, $jobId);
        $updateJobStmt->execute();
        $updateJobStmt->close();
        echo json_encode(array("message" => "Job updated"));
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
