<?php
$createJobApplicationSchema = [
    "userId" => [
        "required" => true,
        "message" => "UserId is required",
    ],
    "jobId" => [
        "required" => true,
        "message" => "JobId is required",
    ]
];
$updateJobApplicationSchema = [
    "jobApplicationUserId" => [
        "required" => true,
        "message" => "JobApplicationUserId is required",
    ],
    "jobId" => [
        "required" => true,
        "message" => "JobId is required",
    ],
    "status" => [
        "required" => true,
        "message" => "Status is required"
    ]
];

$getJobApplicationsSchema = [
    "jobId" => [
        "required" => true,
        "message" => "JobId is required",
    ],
    "agentUserId" => [
        "required" => true,
        "message" => "AgentUserId is required",
    ],
];
$getApplicantsSchema = [
    "applicantUserId" => [
        "required" => true,
        "message" => "ApplicantUserId is required"
    ]
];
$validStatuses = ['ACCEPTED', 'REJECTED'];
