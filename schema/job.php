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
];

$updateJobSchema
    = [
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
        "jobId" => [
            "required" => true,
            "message" => "JobId is required"
        ]
    ];
