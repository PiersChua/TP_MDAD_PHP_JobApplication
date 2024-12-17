<?php
require_once __DIR__ . "/../lib/db.php";

$db = Db::getInstance();

$sql = "
INSERT INTO jobs (
    position,
    responsibilities,
    description,
    location,
    schedule,
    organisation,
    partTimeSalary,
    fullTimeSalary,
    userId
) VALUES (
    'Hotel Banquet Waiter',
    'Assist with event preparation by setting up tables, chairs, and decorations according to the event’s specifications. During the banquet, you will attend to guest requests promptly, ensuring their needs are met with professionalism and courtesy. Maintain cleanliness and organization throughout the event, including clearing plates, refilling drinks, and keeping the banquet area tidy. Collaborate closely with kitchen staff to ensure timely and accurate food and beverage service, addressing any issues that arise promptly to create a positive and seamless guest experience. After the event, assist with post-event tasks such as clearing tables, resetting the banquet space, and ensuring all equipment and supplies are accounted for. Uphold hygiene and safety standards throughout the event to maintain a clean and welcoming environment. Your role will be pivotal in contributing to the success of the event, leaving guests with a lasting impression of excellent service and hospitality.',
    'Royal Plaza is seeking enthusiastic and customer-focused waiters to deliver exceptional dining experiences. Responsibilities include taking orders, serving food and beverages, ensuring table cleanliness, and attending to guest requests promptly. Join our team and be part of a dynamic environment committed to excellence in hospitality!

    Attire Requirement:
    Full black with flat leather shoes.
    No visible tattoos or colored hair.',
    'Royal Plaza Hotel, Singapore',
    'Part-Time',
    'Royal Plaza',
    12.00,
    NULL,
    '1930d544-bc75-11ef-9402-88a4c25ac32d'
);
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
