<?php

return [
    'branch_name' => env('BRANCH', 'master'),
    'message' => env('BRANCH_MESSAGE', 'Dangerous behaviour, someone try to change branch on live!'),
    'phone_number' => env('BRANCH_PHONE_NUMBER'),
];
