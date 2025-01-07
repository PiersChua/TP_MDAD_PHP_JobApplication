<?php

$createAgencyApplicationSchema = [
    "name" => [
        "required" => "true",
        "message" => "Agency Name is required",
    ],
    "email" => [
        "required" => true,
        "message" => "Agency Email is required",
    ],
    "phoneNumber" => [
        "required" => true,
        "message" => "Agency Phone Number is required",
    ],
    "address" => [
        "required" => true,
        "message" => "Agency Adress is required",
    ],
];

$updateAgencyApplicationSchema = [
    "agencyApplicationId" => [
        "required" => "true",
        "message" => "AgencyApplicationId is required",
    ],
    "status" => [
        "required" => true,
        "message" => "Status is required",
    ],
];
$getApplicantsSchema = [
    "agencyApplicationId" => [
        "required" => true,
        "message" => "AgencyApplicationId is required"
    ]
];
$validStatuses = ['ACCEPTED', 'REJECTED'];
