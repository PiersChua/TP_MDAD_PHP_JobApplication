<?php
require_once __DIR__ . "/../../schema/password-reset-otp.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../utils/stringUtils.php";


$result = Validation::validateSchema($_POST, $verifyOtpSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$email, $otp] = [StringUtils::lowercaseEmail($_POST["email"]), $_POST["otp"]];
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT userId FROM users WHERE email=?");
        $findExistingUserStmt->bind_param("s", $email);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($userId);
        $findExistingUserStmt->fetch();
        $findExistingUserStmt->close();
        if ($userId === null) {
            http_response_code(404);
            echo json_encode(array("message" => "Email not found"));
            exit();
        }
        $getOtpStmt = $db->getConnection()->prepare("SELECT createdAt FROM password_reset_otps WHERE userId = ?");
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
            $deleteOtpStmt = $db->getConnection()->prepare("DELETE FROM password_reset_otps WHERE userId = ?");
            $deleteOtpStmt->bind_param("s", $userId);
            $deleteOtpStmt->execute();
            $deleteOtpStmt->close();
        }

        $createOtpStmt = $db->getConnection()->prepare("INSERT INTO password_reset_otps (userId,otp) VALUES (?,?)");
        $createOtpStmt->bind_param("ss", $userId, $otp);
        $createOtpStmt->execute();
        $createOtpStmt->close();
        echo json_encode(array("message" => "OTP sent"));
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
