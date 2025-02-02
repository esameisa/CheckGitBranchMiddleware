<?php

return [
    /*
     * branch name that will check if it's the current branch
     */
    'branch_name' => env('BRANCH', 'master'),

    /*
     * phone number that will send mora sms message on it if mora is exist
     */
    'phone_number' => env('BRANCH_PHONE_NUMBER'),

    /*
     * message that will send via mora
     */
    'message' => env('BRANCH_MESSAGE', 'Dangerous behaviour, someone try to change branch on live!'),

    /*
     * `branch_name` that mean force checkout again to `BRANCH`
     * `abort_404` that mean force abort 404
     */
    'force_checkout' => env('FORCE_CHECKOUT', 'branch_name'),
];
