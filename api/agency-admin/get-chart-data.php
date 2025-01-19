<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../utils/userValidator.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_GET["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$userId = $_GET["userId"];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());

        $getAgentRaceProportionStmt = $db->getConnection()->prepare("
        SELECT race, COUNT(*) as race_user_count FROM users
        INNER JOIN agencies ON users.agencyId=agencies.agencyId
        WHERE agencies.userId=?
        GROUP BY RACE
        ");
        $getAgentRaceProportionStmt->bind_param("s", $userId);
        $getAgentRaceProportionStmt->execute();
        $raceResult = $getAgentRaceProportionStmt->get_result();
        $getAgentRaceProportionStmt->close();
        $agentRaceProportion = $raceResult->fetch_all(MYSQLI_ASSOC);

        $getAgentJobProportionStmt = $db->getConnection()->prepare("
        SELECT users.fullName, COUNT(jobs.jobId) as agent_job_count FROM users
        INNER JOIN agencies ON users.agencyId=agencies.agencyId
        INNER JOIN jobs on users.userId=jobs.userId
        WHERE agencies.userId=?
        GROUP BY users.fullName
        LIMIT 3
        ");
        $getAgentJobProportionStmt->bind_param("s", $userId);
        $getAgentJobProportionStmt->execute();
        $jobResult = $getAgentJobProportionStmt->get_result();
        $getAgentJobProportionStmt->close();
        $agentJobProportion = $jobResult->fetch_all(MYSQLI_ASSOC);

        $getJobApplicationStatusProportiontStmt = $db->getConnection()->prepare("
        SELECT status, COUNT(*) as job_application_count FROM job_applications
        INNER JOIN jobs ON jobs.jobId=job_applications.jobId
        INNER JOIN users on jobs.userId=users.userId
        INNER JOIN agencies ON users.agencyId=agencies.agencyId
        WHERE agencies.userId=?
        GROUP BY status
        LIMIT 3
        ");
        $getJobApplicationStatusProportiontStmt->bind_param("s", $userId);
        $getJobApplicationStatusProportiontStmt->execute();
        $jobApplicationResult = $getJobApplicationStatusProportiontStmt->get_result();
        $getJobApplicationStatusProportiontStmt->close();
        $jobApplicationProportion = $jobApplicationResult->fetch_all(MYSQLI_ASSOC);

        $data = array("race_data" => $agentRaceProportion, "job_data" => $agentJobProportion, "job_application_data" => $jobApplicationProportion);
        echo json_encode($data);
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
