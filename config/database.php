<?php

require_once __DIR__ . "/../lib/db.php";

$db = new DB_CONNECTION();

$sql = "
CREATE TABLE IF NOT EXISTS agencies (
    agencyId CHAR(36) NOT NULL DEFAULT (UUID()), 
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    address VARCHAR(255),
    PRIMARY KEY (agencyId)
);

CREATE TABLE IF NOT EXISTS users (
    userId VARCHAR(36) NOT NULL DEFAULT (UUID()), 
    fullName VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phoneNumber INT NOT NULL,
    dateOfBirth DATE,
    role ENUM('JobSeeker', 'Agent', 'Admin') NOT NULL,
    gender ENUM('Male', 'Female'),
    nationality ENUM('Singaporean', 'PR', 'Others'),
    resume VARCHAR(255),
    agencyId CHAR(36),
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userId),
    FOREIGN KEY (agencyId) REFERENCES agencies(agencyId)
);
";
if ($db->connect()) {
    if (mysqli_multi_query($db->dbConnection, $sql)) {
        echo "Tables created successfully!";
    } else {
        echo "Error creating tables: " . mysqli_error($db->dbConnection);
    }
} else {
    echo "Connection failed: " . mysqli_connect_error();
}
