<?php
require_once __DIR__ . "/../../schema/agency-application.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
$result = Validation::validateSchema($_POST, $agencyApplicationSchema);

if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result, "type" => "Error"));
    exit();
}
// todo: change this to extract the userId from jwt token from headers
[$agencyName, $agencyEmail, $agencyPhoneNumber, $agencyAddress, $userId] = [
    $_POST["agencyName"],
    $_POST["agencyEmail"],
    $_POST["agencyPhoneNumber"],
    $_POST["agencyAddress"] ?? null,
    $_POST["userId"]
];
if (!Validation::validateEmail($agencyEmail)) {
    echo json_encode(array("message" => "Invalid email type, please type in the correct format", "type" => "Error"));
    exit();
}
if (!Validation::validatePhoneNumber($agencyPhoneNumber)) {
    echo json_encode(array("message" => "Invalid phone number, please type in the correct format", "type" => "Error"));
    exit();
}
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT COUNT(*) from users WHERE userId=? AND role='JobSeeker'");
        $findExistingUserStmt->bind_param("s", $userId);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($userCount);
        $findExistingUserStmt->fetch();
        $findExistingUserStmt->close();
        if ($userCount === 0) {
            http_response_code(404);
            echo json_encode(array("message" => "User not found", "type" => "Error"));
            exit();
        }

        $findExistingAgencyStmt = $db->getConnection()->prepare("SELECT COUNT(*) from agencies WHERE name=? OR email=? OR phoneNumber=?");
        $findExistingAgencyStmt->bind_param("sss", $agencyName, $agencyEmail, $agencyPhoneNumber);
        $findExistingAgencyStmt->bind_result($agencyCount);
        $findExistingAgencyStmt->execute();
        $findExistingAgencyStmt->fetch();
        $findExistingAgencyStmt->close();
        if ($agencyCount > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Agency already exists", "type" => "Error"));
            exit();
        }

        $createAgencyStmt = $db->getConnection()->prepare("INSERT INTO agency_applications (agencyName, agencyEmail, agencyPhoneNumber, agencyAddress, userId) VALUES(?,?,?,?,?) ");
        $createAgencyStmt->bind_param("sssss", $agencyName, $agencyEmail, $agencyPhoneNumber, $agencyAddress, $userId);
        $createAgencyStmt->execute();
        $createAgencyStmt->close();
        echo json_encode(array("message" => "Agency application submitted", "type" => "Success"));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage(), "type" => "Error"));
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to connect to the database", "type" => "Error"));
    $db->close();
}
