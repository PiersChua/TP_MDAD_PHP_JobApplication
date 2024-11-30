<?php

$agencyApplicationSchema = [
    "agencyName" => [
        "required" => "true",
        "message" => "Agency Name is required",
    ],
    "agencyEmail" => [
        "required" => true,
        "message" => "Agency Email is required",
    ],
    "agencyPhoneNumber" => [
        "required" => true,
        "message" => "Agency Phone Number is required",
    ],
    "agencyAddress" => [
        "required" => false
    ],
    "userId" => [
        "required" => true,
    ]
];
