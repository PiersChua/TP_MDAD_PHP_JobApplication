<?php

$jobSchema = [
    "position" => [
        "required" => true,
        "message" => "Position is required"
    ],
    "responsibilities" => [
        "required" => true,
        "message" => "Responsibilities is required"
    ],
    "description" => [
        "required" => true,
        "message" => "Description is required"
    ],
    "isPartTime" => [
        "required" => true,
        "message" => "Employment type is required"
    ],
    "isFullTime" => [
        "required" => true,
        "message" => "Employment type is required"
    ],
    "location" => [
        "required" => true,
        "message" => "Location is required"
    ],
    "schedule" => [
        "required" => true,
        "message" => "Schedule is required"
    ],
    "organisation" => [
        "required" => true,
        "message" => "Organisation is required"
    ],
    "partTimeSalary" => [
        "required" => false,
    ],
    "fullTimeSalary" => [
        "required" => false,
    ],
    "userId" => [
        "required" => true,
        "message" => "UserId is required"
    ],
];
