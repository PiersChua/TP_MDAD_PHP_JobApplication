<?php
require_once __DIR__ . "/../../schema/verification-otp.php";
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
        $findExistingUserStmt = $db->getConnection()->prepare("SELECT userId, role,fullName FROM users WHERE email=?");
        $findExistingUserStmt->bind_param("s", $email);
        $findExistingUserStmt->execute();
        $findExistingUserStmt->bind_result($userId, $role, $fullName);
        if (!$findExistingUserStmt->fetch()) {
            http_response_code(404);
            echo json_encode(array("message" => "User not found"));
            exit();
        }
        $findExistingUserStmt->close();

        $findExistingOtpStmt = $db->getConnection()->prepare("SELECT * FROM verification_otps WHERE userId=?");
        $findExistingOtpStmt->bind_param("s", $userId);
        $findExistingOtpStmt->execute();
        $otpResult = $findExistingOtpStmt->get_result();
        $findExistingOtpStmt->close();
        $userOtp = $otpResult->fetch_assoc();
        if ($userOtp === null) {
            http_response_code(404);
            echo json_encode(array("message" => "OTP does not exist"));
            exit();
        }
        if ($userOtp["otp"] !== $otp) {
            http_response_code(401);
            echo json_encode(array("message" => "OTP is incorrect. Please try again"));
            exit();
        }
        $otpCreatedAtTime = new DateTime($userOtp["createdAt"], new DateTimeZone('Asia/Singapore'));
        $currentTime = new DateTime();
        $timeDifference = $currentTime->getTimestamp() - $otpCreatedAtTime->getTimestamp();
        if ($timeDifference > 3600) {
            http_response_code(400);
            echo json_encode(array("message" => "OTP has expired, please request a new one"));
            exit();
        }
        $deleteOtpStmt = $db->getConnection()->prepare("DELETE FROM verification_otps WHERE userId=?");
        $deleteOtpStmt->bind_param("s", $userId);
        $deleteOtpStmt->execute();
        $deleteOtpStmt->close();
        $updateUserStmt = $db->getConnection()->prepare("UPDATE users SET isVerified = 1 WHERE userId=?");
        $updateUserStmt->bind_param("s", $userId);
        $updateUserStmt->execute();
        $updateUserStmt->close();
        $token = Jwt::encode(array("userId" => $userId, "role" => $role));
        echo json_encode(array("message" => "Verification Successful", "token" => $token, "userId" => $userId, "role" => $role, "fullName" => $fullName));
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
