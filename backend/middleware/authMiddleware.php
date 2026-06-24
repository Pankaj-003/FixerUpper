<?php

declare(strict_types=1);

function bearerToken(): string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches) === 1) {
        return trim($matches[1]);
    }

    return '';
}

function requireAuth(): array
{
    $token = $_COOKIE['fixerupper_token'] ?? bearerToken();
    if (!is_string($token) || $token === '') {
        errorResponse('Authentication is required.', 401);
    }

    $claims = verifyJwt($token);
    $sessionUserId = $_SESSION['user_id'] ?? null;

    if (
        $claims === null
        || !is_int($sessionUserId)
        || (int) $claims['sub'] !== $sessionUserId
    ) {
        errorResponse('Your session is invalid or has expired. Please log in again.', 401);
    }

    return [
        'id' => $sessionUserId,
        'email' => (string) ($claims['email'] ?? ''),
    ];
}
