<?php
$signUpSchema =    [
    "fullName" => [
        "required" => true,
        "message" => "Full Name is required"
    ],
    "email" => [
        "required" => true,
        "message" => "Email is required"
    ],
    "password" => [
        "required" => true,
        "message" => "Password is required"
    ],
    "phoneNumber" => [
        "required" => true,
        "message" => "Phone number is required"
    ],
    "role" => [
        "required" => true,
        "message" => "Role is required"
    ],
];

$loginSchema = [
    "email" => [
        "required" => true,
        "message" => "Email is required"
    ],
    "password" => [
        "required" => true,
        "message" => "Password is required"
    ],
];

$updateUserSchema = [
    "fullName" => [
        "required" => true,
        "message" => "Full Name is required"
    ],
    "email" => [
        "required" => true,
        "message" => "Email is required"
    ],
    "phoneNumber" => [
        "required" => true,
        "message" => "Phone number is required"
    ]
];
