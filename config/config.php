<?php
// Application configuration
return [
    'app_name' => 'Hospital Management System',
    'base_url' => 'http://localhost/hospital_system',
    'timezone' => 'Asia/Tbilisi',
    'debug' => true,
    
    // Session settings
    'session' => [
        'name' => 'hms_session',
        'lifetime' => 7200, // 2 hours
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ],
    
    // CSRF protection
    'csrf' => [
        'token_name' => 'csrf_token',
        'header_name' => 'X-CSRF-TOKEN'
    ]
];