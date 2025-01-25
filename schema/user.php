<?php
$allowedRoles = ["Job Seeker", "Admin"];
$allowedGenders = ["Male", "Female"];
$allowedRaces = ["Chinese", "Malay", "Indian", "Others"];
$allowedNationalities = ["Singaporean", "PR", "Others"];
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
        "message" => "Nationality is required"
    ],
    "otp" => [
        "required" => true,
        "message" => "OTP is required"
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
    "otp" => [
        "required" => true,
        "message" => "OTP is required"
    ]
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
        "message" => "Nationality is required"
    ],
    "userIdToBeUpdated" => [
        "required" => true,
        "message" => "UserIdToBeUpdated is required"
    ]
];

$getAgentsSchema = [
    "agencyAdminUserId" => [
        "required" => true,
        "message" => "AgencyAdminUserId is required"
    ]
];

$getUserDetailsSchema = [
    "userIdToGet" => [
        "required" => true,
        "message" => "UserIdToGet is required"
    ],
    "userId" => [
        "required" => true,
        "message" => "UserId is required"
    ]
];
$verifyUserDetailsSchema = [
    "userId" => [
        "required" => true,
        "message" => "UserId is required"
    ],
    "role" => [
        "required" => true,
        "message" => "Role is required"
    ]
];

$getJobSeekerDetailsSchema = [
    "email" => [
        "required" => true,
        "message" => "Email is required"
    ],

];
$addAgentSchema = [
    "email" => [
        "required" => true,
        "message" => "Email is required"
    ],
    "agencyAdminUserId" => [
        "required" => true,
        "message" => "AgencyAdminUserId is required"
    ],
];
$deleteUserSchema = [
    "userIdToBeDeleted" => [
        "required" => true,
        "message" => "UserIdToBeDeleted is required"
    ],

];

$removeImageSchema = [
    "userIdToBeUpdated" => [
        "required" => true,
        "message" => "UserIdToBeUpdated is required"
    ]
];
