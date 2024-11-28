<?php
require_once __DIR__ . "/../../schema/job.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";

$result = Validation::validateSchema($_POST, $jobSchema);
if ($result === null) {
    if (!$POST_["partTimeSalary" && !$POST_["fullTimeSalary"]]) {
        echo json_encode(array("message" => "At least part-time or full-time salary is required", "type" => "Error"));
        exit();
    }
    [$position, $responsibilities, $description, $isPartTime, $isFullTime, $location, $schedule, $organisation, $partTimeSalary, $fullTimeSalary, $userId] = [
        $POST_["position"],
        $POST_["responsibilities"],
        $POST_["description"],
        $POST_["isPartTime"],
        $POST_["isFullTime"],
        $POST_["location"],
        $POST_["schedule"],
        $POST_["organisation"],
        $POST_["partTimeSalary"] ?? null,
        $POST_["fullTimeSalary"] ?? null,
        $POST_["userId"] // todo: change this to extract the userId from jwt token from headers
    ];
} else {
    http_response_code(400);
    echo json_encode(array("message" => $result, "type" => "Error"));
}
