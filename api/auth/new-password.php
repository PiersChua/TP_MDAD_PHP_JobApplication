<?php
require_once __DIR__ . "/../../schema/user.php";
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/stringUtils.php";
require_once __DIR__ . "/../../utils/jwt.php";


$result = Validation::validateSchema($_POST, $newPasswordSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$email, $password] = [StringUtils::lowercaseEmail($_POST["email"]), $_POST["password"]];
$hashedPassword =
    password_hash($password, PASSWORD_BCRYPT);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        $changeUserPasswordStmt = $db->getConnection()->prepare("UPDATE users SET password=? WHERE email=?");
        $changeUserPasswordStmt->bind_param("ss", $hashedPassword, $email);
        $changeUserPasswordStmt->execute();
        $changeUserPasswordStmt->close();
        echo json_encode(array("message" => "Password has been changed"));
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
