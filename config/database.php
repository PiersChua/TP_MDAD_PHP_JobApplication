<?php

require_once __DIR__ . "/../lib/db.php";

$db = Db::getInstance();

$sql = "
CREATE TABLE IF NOT EXISTS agencies (
    agencyId CHAR(36) NOT NULL DEFAULT (UUID()), 
    name VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    phoneNumber VARCHAR(10) NOT NULL UNIQUE,
    address VARCHAR(255),
    userId CHAR(36) NOT NULL,
    PRIMARY KEY (agencyId),
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    userId VARCHAR(36) NOT NULL DEFAULT (UUID()), 
    fullName VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phoneNumber VARCHAR(10) NOT NULL UNIQUE,
    dateOfBirth DATE,
    role ENUM('Job Seeker', 'Agent', 'Agency Admin', 'Admin') NOT NULL,
    gender ENUM('Male', 'Female'),
    race ENUM('Chinese','Malay', 'Indian','Others'),
    nationality ENUM('Singaporean', 'PR', 'Others'),
    resume VARCHAR(255),
    agencyId CHAR(36),
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userId),
    FOREIGN KEY (agencyId) REFERENCES agencies(agencyId)
);

ALTER TABLE agencies 
ADD FOREIGN KEY (userId) REFERENCES users(userId);

CREATE TABLE IF NOT EXISTS jobs(
    jobId CHAR(36) NOT NULL DEFAULT (UUID()), 
    position VARCHAR(255) NOT NULL,
    responsibilities VARCHAR(1000) NOT NULL,
    description VARCHAR(1000) NOT NULL,
    location VARCHAR(255) NOT NULL,
    schedule VARCHAR(255) NOT NULL,
    organisation VARCHAR(255) NOT NULL,
    partTimeSalary DECIMAL(6,2),
    fullTimeSalary DECIMAL(8,2),
    userId CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (jobId),
    FOREIGN KEY (userId) REFERENCES users(userId)
);  

CREATE TABLE IF NOT EXISTS job_applications(
    status ENUM ('PENDING','ACCEPTED','REJECTED') NOT NULL DEFAULT 'PENDING',
    userId CHAR(36) NOT NULL,
    jobId CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userId,jobId)
);

CREATE TABLE IF NOT EXISTS favourite_jobs(
    userId CHAR(36) NOT NULL,
    jobId CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userId,jobId)
);

CREATE TABLE IF NOT EXISTS agency_applications(
    agencyApplicationId CHAR(36) NOT NULL DEFAULT (UUID()),
    status ENUM ('PENDING','ACCEPTED','REJECTED') NOT NULL DEFAULT 'PENDING',
    name VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    phoneNumber VARCHAR(10) NOT NULL UNIQUE,
    address VARCHAR(255),
    userId CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (agencyApplicationId),
    FOREIGN KEY (userId) REFERENCES users(userId)
);
";
if ($db->getConnection()) {
    if (mysqli_multi_query($db->getConnection(), $sql)) {
        echo "Tables created successfully!";
    } else {
        echo "Error creating tables: " . mysqli_error($db->getConnection());
    }
    $db->close();
} else {
    echo "Connection failed: " . mysqli_connect_error();
}
