<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CORS Configuration
|--------------------------------------------------------------------------
|
| Allows requests from your Vercel frontend and local development.
|
*/

$allowedOrigins = [
    'https://fixer-upper-beta.vercel.app',
    'http://localhost:5500',
    'http://127.0.0.1:5500',
    'http://localhost:3000',
    'http://127.0.0.1:3000',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
header('Access-Control-Max-Age: 86400');

/*
|--------------------------------------------------------------------------
| Handle Preflight Requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}