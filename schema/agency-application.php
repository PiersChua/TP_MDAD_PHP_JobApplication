<?php

$agencyApplicationSchema = [
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
        "required" => false
    ],
];
