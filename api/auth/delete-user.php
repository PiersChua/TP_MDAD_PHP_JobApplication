<?php
require_once __DIR__ . "/../../utils/validation.php";
require_once __DIR__ . "/../../lib/db.php";
require_once __DIR__ . "/../../utils/jwt.php";
require_once __DIR__ . "/../../schema/user.php";

$headers = apache_request_headers();
$token = Jwt::getTokenFromHeader($headers);
if (!isset($_POST["userId"]) || is_null($token)) {
    http_response_code(400);
    echo json_encode(array("message" => "UserId and Token is required"));
    exit();
}
$result = Validation::validateSchema($_POST, $deleteUserSchema);
if ($result !== null) {
    http_response_code(400);
    echo json_encode(array("message" => $result));
    exit();
}
[$userId, $userIdToBeDeleted] = [$_POST["userId"], $_POST["userIdToBeDeleted"]];
/**
 *  Verify token
 */
$payload = Jwt::decode($token);
Jwt::verifyPayloadWithUserId($payload, $userId);
$db = Db::getInstance();
if ($db->getConnection()) {
    try {
        try {
            $connection = $db->getConnection();
            $connection->begin_transaction();

            $deleteUserStmt = $db->getConnection()->prepare("
            DELETE FROM users
            WHERE userId=?
            ");
            $deleteUserStmt->bind_param("s", $userIdToBeDeleted);
            $deleteUserStmt->execute();
            $deleteUserStmt->close();

            $connection->commit();
            echo json_encode(array("message" => "Action successful. All associated records deleted"));
        } catch (Exception $e) {
            $connection->rollback();
            http_response_code(500);
            echo json_encode(array("message" => "Transaction failed: " . $e->getMessage()));
        }
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
