<?php

declare(strict_types=1);

function base64UrlEncode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function base64UrlDecode(string $value): string|false
{
    $remainder = strlen($value) % 4;
    if ($remainder !== 0) {
        $value .= str_repeat('=', 4 - $remainder);
    }

    return base64_decode(strtr($value, '-_', '+/'), true);
}

function jwtSecret(): string
{
    $secret = getenv('JWT_SECRET') ?: '';
    if (strlen($secret) < 32) {
        throw new RuntimeException('JWT_SECRET must be at least 32 characters.');
    }

    return $secret;
}

function createJwt(int $userId, string $email): string
{
    $now = time();
    $ttl = max(300, (int) (getenv('JWT_TTL') ?: 3600));

    $header = base64UrlEncode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT',
    ], JSON_THROW_ON_ERROR));

    $payload = base64UrlEncode(json_encode([
        'sub' => (string) $userId,
        'email' => $email,
        'iat' => $now,
        'exp' => $now + $ttl,
        'iss' => getenv('APP_URL') ?: 'fixerupper-api',
    ], JSON_THROW_ON_ERROR));

    $signature = hash_hmac('sha256', "{$header}.{$payload}", jwtSecret(), true);

    return "{$header}.{$payload}." . base64UrlEncode($signature);
}

function verifyJwt(string $token): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
    $expected = hash_hmac(
        'sha256',
        "{$encodedHeader}.{$encodedPayload}",
        jwtSecret(),
        true
    );
    $provided = base64UrlDecode($encodedSignature);

    if ($provided === false || !hash_equals($expected, $provided)) {
        return null;
    }

    $decodedPayload = base64UrlDecode($encodedPayload);
    if ($decodedPayload === false) {
        return null;
    }

    try {
        $payload = json_decode($decodedPayload, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return null;
    }

    if (
        !is_array($payload)
        || !isset($payload['sub'], $payload['exp'])
        || !ctype_digit((string) $payload['sub'])
        || (int) $payload['exp'] <= time()
    ) {
        return null;
    }

    return $payload;
}

function authCookieOptions(int $expires): array
{
    $secure = filter_var(getenv('COOKIE_SECURE') ?: 'true', FILTER_VALIDATE_BOOL);
    $sameSite = getenv('COOKIE_SAMESITE') ?: ($secure ? 'None' : 'Lax');

    return [
        'expires' => $expires,
        'path' => '/',
        'domain' => getenv('COOKIE_DOMAIN') ?: '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => $sameSite,
    ];
}

function setAuthCookie(string $token): void
{
    $ttl = max(300, (int) (getenv('JWT_TTL') ?: 3600));
    setcookie('fixerupper_token', $token, authCookieOptions(time() + $ttl));
}

function clearAuthCookie(): void
{
    setcookie('fixerupper_token', '', authCookieOptions(time() - 3600));
}
