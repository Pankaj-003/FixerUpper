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

function currentAuthUser(): ?array
{
    $token = $_COOKIE['fixerupper_token'] ?? bearerToken();
    if (!is_string($token) || $token === '') {
        return null;
    }

    try {
        $claims = verifyJwt($token);
    } catch (Throwable) {
        return null;
    }

    $sessionUserId = $_SESSION['user_id'] ?? null;

    if (
        $claims === null
        || !is_int($sessionUserId)
        || (int) $claims['sub'] !== $sessionUserId
    ) {
        return null;
    }

    return [
        'id' => $sessionUserId,
        'email' => (string) ($claims['email'] ?? ''),
    ];
}

function requireAuth(): array
{
    $user = currentAuthUser();

    if ($user === null) {
        errorResponse('Your session is invalid or has expired. Please log in again.', 401);
    }

    return $user;
}
