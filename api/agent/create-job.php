<?php
require_once __DIR__ . "/../../schema/job.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";

$result = Validation::validateSchema($_POST, $jobSchema);
if ($result === null) {
    if (!isset($_POST["partTimeSalary"]) && !isset($_POST["fullTimeSalary"])) {
        echo json_encode(array("message" => "At least part-time or full-time salary is required", "type" => "Error"));
        exit();
    }
    [$position, $responsibilities, $description, $location, $schedule, $organisation, $partTimeSalary, $fullTimeSalary, $userId] = [
        $_POST["position"],
        $_POST["responsibilities"],
        $_POST["description"],
        $_POST["location"],
        $_POST["schedule"],
        $_POST["organisation"],
        $_POST["partTimeSalary"] ?? null,
        $_POST["fullTimeSalary"] ?? null,
        $_POST["userId"] // todo: change this to extract the userId from jwt token from headers
    ];
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
                echo json_encode(array("message" => "Agent not found", "type" => "Error"));
                $db->close();
                exit();
            }

            $createJobStmt = $db->getConnection()->prepare("INSERT INTO jobs (position, responsibilities, description, location, schedule, organisation, partTimeSalary, fullTimeSalary, userId) VALUES (?,?,?,?,?,?,?,?,?)");
            $createJobStmt->bind_param("ssssssdds", $position, $responsibilities, $description, $location, $schedule, $organisation, $partTimeSalary, $fullTimeSalary, $userId);
            $createJobStmt->execute();
            $createJobStmt->close();
            echo json_encode(array("message" => "Job created", "type" => "Success"));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("message" => $e->getMessage(), "type" => "Error"));
        }
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => $result, "type" => "Error"));
}
