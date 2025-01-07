<?php

class UserValidator
{
    public static function verifyIfUserExists($userId, $role, mysqli|null $dbConnection)
    {
        $findUserStmt = $dbConnection->prepare("SELECT COUNT(*) FROM users WHERE userId=? AND role=?");
        $findUserStmt->bind_param("ss", $userId, $role);
        $findUserStmt->execute();
        $findUserStmt->bind_result($userCount);
        $findUserStmt->fetch();
        $findUserStmt->close();
        if ($userCount === 0) {
            http_response_code(403);
            echo json_encode(array("message" => "User does not exist in database"));
            exit();
        }
    }
}
