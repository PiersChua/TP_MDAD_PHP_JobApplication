<?php
require_once __DIR__ . "/../lib/db.php";

$db = Db::getInstance();


$sql = "

INSERT INTO users (fullName, email, password, phoneNumber, dateOfBirth, role, gender, race, nationality)
VALUES
    ('Alice Johnson', 'alice@example.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '1234567890', '1990-01-01', 'Job Seeker', 'Female', 'Chinese', 'Singaporean'),
    ('Bob Smith', 'bob@example.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '1234567891', '1985-06-15', 'Agent', 'Male', 'Malay', 'PR'),
    ('Charlie Brown', 'charlie@example.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '1234567892', '1980-03-22', 'Agency Admin', 'Male', 'Indian', 'Singaporean'),
    ('Diana Prince', 'diana@example.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '1234567893', '1995-09-10', 'Admin', 'Female', 'Others', 'Singaporean');


INSERT INTO agencies (name, email, phoneNumber, address, userId)
VALUES
    ('Tech Innovations', 'tech@example.com', '9876543210', '123 Tech Park', (SELECT userId FROM users WHERE email = 'charlie@example.com')),
    ('Green Solutions', 'green@example.com', '9876543211', '456 Green Lane', (SELECT userId FROM users WHERE email = 'charlie@example.com'));


UPDATE users
SET agencyId = (SELECT agencyId FROM agencies WHERE name = 'Tech Innovations')
WHERE email = 'bob@example.com';


INSERT INTO jobs (position, responsibilities, description, location, schedule, organisation, partTimeSalary, fullTimeSalary, userId)
VALUES
    ('Software Developer', 'Develop and maintain software.', 'Exciting opportunity to work on cutting-edge projects.', 'Tech Park', 'Mon-Fri 9am-6pm', 'Tech Innovations', 25.00, 5000.00, (SELECT userId FROM users WHERE email = 'bob@example.com')),
    ('Project Manager', 'Manage and oversee projects.', 'Lead teams to success in various projects.', 'Green Lane', 'Mon-Fri 9am-5pm', 'Green Solutions', NULL, 7000.00, (SELECT userId FROM users WHERE email = 'bob@example.com'));
";

if ($db->getConnection()) {
    if (mysqli_multi_query($db->getConnection(), $sql)) {
        echo "Data seeded successfully!";
    } else {
        echo "Error creating tables: " . mysqli_error($db->getConnection());
    }
    $db->close();
} else {
    echo "Connection failed: " . mysqli_connect_error();
}
