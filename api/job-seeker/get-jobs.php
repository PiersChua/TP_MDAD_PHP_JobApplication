<?php
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/userValidator.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);

if (!isset($_GET["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}

$userId = $_GET["userId"];
$query = isset($_GET["query"]) ? "%" . strtolower($_GET["query"]) . "%" : null;
$jobType = isset($_GET["jobType"]) ? $_GET["jobType"] : null;
$minSalary = isset($_GET["minSalary"]) ? $_GET["minSalary"] : null;

/**
 * Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);

$db = Db::getInstance();

if ($db->getConnection()) {
    try {
        UserValidator::verifyIfUserExists($userId, $payload["role"], $db->getConnection());
        $sql = "
            SELECT jobs.*, agencies.name as agency_name 
            FROM jobs
            INNER JOIN users ON jobs.userId = users.userId
            INNER JOIN agencies ON users.agencyId = agencies.agencyId
            WHERE 1=1
        ";
        $types = "";
        $params = [];
        if (!empty($query)) {
            $sql .= " AND LOWER(jobs.position) LIKE ?";
            $types .= "s";
            $params[] = $query;
        }
        if (!empty($jobType) && !empty($minSalary)) {
            if ($jobType === "part-time") {
                $sql .= " AND jobs.partTimeSalary >= ?";
                $types .= "d";
                $params[] = $minSalary;
            } elseif ($jobType === "full-time") {
                $sql .= " AND jobs.fullTimeSalary >=?";
                $types .= "d";
                $params[] = $minSalary;
            }
        }
        $sql .= " ORDER BY jobs.updatedAt DESC";
        $stmt = $db->getConnection()->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $jobs = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(array("data" => $jobs));
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
