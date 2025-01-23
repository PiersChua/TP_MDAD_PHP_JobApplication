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
        $getUserNationalityProportionStmt = $db->getConnection()->prepare("
        SELECT nationality, COUNT(*) as nationality_user_count FROM users
        WHERE role!='Admin'
        GROUP BY nationality
        ");
        $getUserNationalityProportionStmt->execute();
        $nationalityResullt = $getUserNationalityProportionStmt->get_result();
        $getUserNationalityProportionStmt->close();
        $userNationalityProportion = $nationalityResullt->fetch_all(MYSQLI_ASSOC);

        $getUserRoleProportionStmt = $db->getConnection()->prepare("
        SELECT role, COUNT(*) AS role_user_count FROM users
        WHERE role != 'Admin'
        GROUP BY ROLE
        ");
        $getUserRoleProportionStmt->execute();
        $roleResult = $getUserRoleProportionStmt->get_result();
        $getUserRoleProportionStmt->close();
        $userRoleProportion = $roleResult->fetch_all(MYSQLI_ASSOC);

        $getAgencyJobProportionStmt = $db->getConnection()->prepare("
        SELECT agencies.name, COUNT(jobs.jobId) AS agency_job_count FROM agencies
        INNER JOIN users ON users.agencyId=agencies.agencyId
        INNER JOIN jobs ON jobs.userId=users.userId
        GROUP BY agencies.agencyId
        ORDER BY agency_job_count DESC
        LIMIT 3
        ");
        $getAgencyJobProportionStmt->execute();
        $jobResult = $getAgencyJobProportionStmt->get_result();
        $getAgencyJobProportionStmt->close();
        $agencyJobProportion = $jobResult->fetch_all(MYSQLI_ASSOC);

        $data = array("nationality_data" => $userNationalityProportion, "role_data" => $userRoleProportion, "job_data" => $agencyJobProportion);
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
