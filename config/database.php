<?php

require_once __DIR__ . "/../lib/db.php";

$db = Db::getInstance();

$sql = "
CREATE TABLE IF NOT EXISTS agencies (
    agencyId CHAR(36) NOT NULL DEFAULT (UUID()), 
    name VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    phoneNumber VARCHAR(10) NOT NULL UNIQUE,
    address VARCHAR(255) NOT NULL,
    image MEDIUMBLOB,
    userId CHAR(36) NOT NULL,
    createdAt TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (agencyId)
);

CREATE TABLE IF NOT EXISTS users (
    userId VARCHAR(36) NOT NULL DEFAULT (UUID()), 
    fullName VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phoneNumber VARCHAR(10) NOT NULL UNIQUE,
    isVerified TINYINT(1) DEFAULT 0,
    dateOfBirth DATE,
    role ENUM('Job Seeker', 'Agent', 'Agency Admin', 'Admin') NOT NULL,
    gender ENUM('Male', 'Female'),
    race ENUM('Chinese','Malay', 'Indian','Others'),
    nationality ENUM('Singaporean', 'PR', 'Others'),
    image MEDIUMBLOB,
    agencyId CHAR(36),
    createdAt TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userId),
    FOREIGN KEY (agencyId) REFERENCES agencies(agencyId) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS verification_otps(
    userId VARCHAR(36) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    createdAt TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userId, otp),
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS password_reset_otps(
    userId VARCHAR(36) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    createdAt TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userId, otp),
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
);

ALTER TABLE agencies 
ADD FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE;

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
    createdAt TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (jobId),
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
);  

CREATE TABLE IF NOT EXISTS job_applications(
    status ENUM ('PENDING','ACCEPTED','REJECTED') NOT NULL DEFAULT 'PENDING',
    userId CHAR(36) NOT NULL,
    jobId CHAR(36) NOT NULL,
    createdAt TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userId,jobId),
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
    FOREIGN KEY (jobId) REFERENCES jobs(jobId) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS favourite_jobs(
    userId CHAR(36) NOT NULL,
    jobId CHAR(36) NOT NULL,
    createdAt TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userId,jobId),
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
    FOREIGN KEY (jobId) REFERENCES jobs(jobId) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS agency_applications(
    agencyApplicationId CHAR(36) NOT NULL DEFAULT (UUID()),
    status ENUM ('PENDING','ACCEPTED','REJECTED') NOT NULL DEFAULT 'PENDING',
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phoneNumber VARCHAR(10) NOT NULL,
    address VARCHAR(255) NOT NULL,
    image MEDIUMBLOB,
    userId CHAR(36) NOT NULL,
    createdAt TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (agencyApplicationId),
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
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
