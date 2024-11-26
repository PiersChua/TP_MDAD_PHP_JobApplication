<?php
if (isset($_POST["fullName"]) && isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["phoneNumber"])) {
    $fullName = $_POST["fullName"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $phoneNumber = $_POST["phoneNumber"];

    require_once __DIR__ . "/../../lib/db.php";
    $db = new DB_CONNECTION();
    $db->connect();

    if ($db->dbConnection) {
        try {

            $findExistingUserStmt = $db->dbConnection->prepare("SELECT COUNT(*) from users WHERE email=?");
            $findExistingUserStmt->bind_param("s", $email);
            $findExistingUserStmt->execute();
            $findExistingUserStmt->bind_result($count);
            if (!$count > 0) {
                http_response_code(400);
                echo json_encode(array("message" => "Email already exists", "type" => "Error"));
                $db->close($db->dbConnection);
                exit;
            }

            $$createNewUserStmt = $db->dbConnection->prepare("INSERT INTO users (fullName, email, password, phoneNumber, role) VALUES (?,?,?,?)");
            $createNewUserStmt->bind_param("sssis", $fullName, $email, $password, $phoneNumber, "JobSeeker");
            $createNewUserStmt->execute();
            echo json_encode(array("message" => "User successfully created", "type" => "Success"));
            $findExistingUserStmt->close();
            $createNewUserStmt->close();
        } catch (Exception $e) {
            $createNewUserStmt->close();
            http_response_code(500);
            echo json_encode(array("message" => $e->getMessage(), "type" => "Error"));
        }
        $db->close($db->dbConnection);
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to connect to database", "type" => "Error"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Please fill in the required fields", "type" => "Error"));
}
