<?php
$updateAgencySchema = [
    "name" => [
        "required" => true,
        "message" => "Name is required",
    ],
    "email" => [
        "required" => true,
        "message" => "email is required",
    ],
    "phoneNumber" => [
        "required" => true,
        "message" => "Phone Number is required",
    ],
    "address" => [
        "required" => false,
    ],
    "agencyAdminUserId" => [
        "required" => true,
        "message" => "AgencyAdminUserId is required",
    ],
];
$getAgencyDetailsSchema = [
    "userId" => [
        "required" => true,
        "message" => "UserId is required"
    ],
    "agencyAdminUserId" => [
        "required" => true,
        "message" => "AgencyAdminUserId is required"
    ]
];

$removeImageSchema = [
    "userId" => [
        "required" => true,
        "message" => "UserId is required"
    ],
    "agencyAdminUserId" => [
        "required" => true,
        "message" => "AgencyAdminUserId is required"
    ]
];
