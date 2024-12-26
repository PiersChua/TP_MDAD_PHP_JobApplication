<?php
$jobApplicationSchema = [
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
$validStatuses = ['ACCEPTED', 'REJECTED'];
