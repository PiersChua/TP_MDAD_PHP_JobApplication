<?php
$allowedRoles = ["Job Seeker", "Admin"];
$allowedGender = ["Male", "Female"];
$allowedRace = ["Chinese", "Malay", "Indian", "Others"];
$allowedNationality = ["Singaporean", "PR", "Others"];
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
    "dateOfBirth" => [
        "required" => true,
        "message" => "Date of Birth is required"
    ],
    "gender" => [
        "required" => true,
        "message" => "Gender is required"
    ],
    "race" => [
        "required" => true,
        "message" => "Race is required"
    ],
    "nationality" => [
        "required" => true,
        "message" => "Nationaliy is required"
    ]
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
    ],
    "dateOfBirth" => [
        "required" => true,
        "message" => "Date of Birth is required"
    ],
    "gender" => [
        "required" => true,
        "message" => "Gender is required"
    ],
    "race" => [
        "required" => true,
        "message" => "Race is required"
    ],
    "nationality" => [
        "required" => true,
        "message" => "Nationaliy is required"
    ]
];
