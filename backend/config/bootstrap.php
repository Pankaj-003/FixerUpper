<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/jwt.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/security.php';

set_exception_handler(static function (Throwable $exception): never {
    $isProduction = strtolower(getenv('APP_ENV') ?: 'production') === 'production';
    error_log(sprintf(
        '[FixerUpper] %s in %s:%d',
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));

    $message = $isProduction
        ? 'An unexpected server error occurred.'
        : $exception->getMessage();

    errorResponse($message, 500);
});
