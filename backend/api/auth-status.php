<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../middleware/authMiddleware.php';

requireMethod('GET');

$user = currentAuthUser();

successResponse([
    'authenticated' => $user !== null,
    'user' => $user === null ? null : [
        'id' => $user['id'],
        'email' => e($user['email']),
    ],
], 'Authentication status retrieved.');
