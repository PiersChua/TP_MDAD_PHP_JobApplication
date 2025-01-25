<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../schema/user-otp.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/stringUtils.php";

$result = Validation::validateSchema($_POST, $createOtpSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$email, $otp] = [
    StringUtils::lowercaseEmail($_POST["email"]),
    $_POST["otp"]
];
$db = Db::getInstance();

if ($db->getConnection()) {
    try {
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT userId FROM users WHERE email=?");
        $findExistingUserStmt->bind_param("s", $email);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($userId);
        if (!$findExistingUserStmt->fetch()) {
            http_response_code(404);
            echo json_encode(array("message" => "user not found"));
            exit();
        }
        $findExistingUserStmt->close();
        $getOtpStmt = $db->getConnection()->prepare("SELECT createdAt FROM user_otps WHERE userId = ?");
        $getOtpStmt->bind_param("s", $userId);
        $getOtpStmt->execute();
        $getOtpStmt->bind_result($otpCreatedAt);
        $getOtpStmt->fetch();
        $getOtpStmt->close();
        if ($otpCreatedAt) {
            $otpCreatedAtTime = new DateTime($otpCreatedAt, new DateTimeZone('Asia/Singapore'));
            $currentTime = new DateTime();
            $timeDifference = $currentTime->getTimestamp() - $otpCreatedAtTime->getTimestamp();
            if ($timeDifference <= 60) {
                $timeRemaining = 60 - $timeDifference;
                http_response_code(400);
                echo json_encode(array("message" => "You have requested too many times. Please wait for $timeRemaining s before continuing"));
                exit();
            }
            $deleteOtpStmt = $db->getConnection()->prepare("DELETE FROM user_otps WHERE userId = ?");
            $deleteOtpStmt->bind_param("s", $userId);
            $deleteOtpStmt->execute();
            $deleteOtpStmt->close();
        }

        $createOtpStmt = $db->getConnection()->prepare("INSERT INTO user_otps (userId,otp) VALUES (?,?)");
        $createOtpStmt->bind_param("ss", $userId, $otp);
        $createOtpStmt->execute();
        $createOtpStmt->close();
        echo json_encode(array("message" => "OTP has been sent!"));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage()));
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to connect to database"));
}
