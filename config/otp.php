<?php
return [
    'expire_minutes' => (int) env('OTP_EXPIRE_MINUTES', 5),
    'enabled' => filter_var(env('OTP_LOGIN_ENABLED', true), FILTER_VALIDATE_BOOL),
    'max_login_attempts' => (int) env('MAX_LOGIN_ATTEMPTS', 5),
];
