<?php
require_once __DIR__ . "/../lib/db.php";

// Get the image ID from the query parameter (e.g., test.php?id=1)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo "Error: Missing or invalid image ID.";
    exit();
}

$imageId = intval($_GET['id']);

$db = Db::getInstance();

if ($db->getConnection()) {
    try {
        // Query to fetch the image from the database
        $sql = "SELECT image FROM agency_applications WHERE agencyApplicationId = ?";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->bind_param("i", $imageId);
        $stmt->execute();
        $stmt->bind_result($imageBlob);
        $stmt->fetch();
        $stmt->close();

        if ($imageBlob) {
            // Set the correct content type header
            header("Content-Type: image/jpeg"); // Or image/png based on your stored format
            echo $imageBlob; // Output the binary image data
        } else {
            http_response_code(404);
            echo "Error: Image not found.";
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    } finally {
        $db->close();
    }
} else {
    http_response_code(500);
    echo "Error: Failed to connect to the database.";
}
