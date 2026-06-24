<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

requireMethod('GET');

successResponse([
    'service' => 'FixerUpper API',
    'version' => '1.0.0',
    'status' => 'healthy',
], 'FixerUpper API is running.');
