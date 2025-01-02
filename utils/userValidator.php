<?php

class UserValidator
{
    public static function verifyIfUserExists($userId, mysqli|null $dbConnection)
    {
        $findUserStmt = $dbConnection->prepare("SELECT COUNT(*) FROM users WHERE userId=?");
        $findUserStmt->bind_param("s", $userId);
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
