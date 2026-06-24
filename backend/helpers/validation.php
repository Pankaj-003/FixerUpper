<?php

declare(strict_types=1);

function cleanText(mixed $value, int $maxLength = 255): string
{
    if (!is_string($value)) {
        return '';
    }

    $value = trim(strip_tags($value));
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';

    return mb_substr($value, 0, $maxLength, 'UTF-8');
}

function cleanEmail(mixed $value): string
{
    $email = strtolower(cleanText($value, 190));
    return filter_var($email, FILTER_SANITIZE_EMAIL) ?: '';
}

function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword(mixed $password): array
{
    if (!is_string($password)) {
        return ['Password is required.'];
    }

    $errors = [];
    if (strlen($password) < 10) {
        $errors[] = 'Password must be at least 10 characters long.';
    }
    if (strlen($password) > 72) {
        $errors[] = 'Password must be 72 characters or fewer.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain an uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain a lowercase letter.';
    }
    if (!preg_match('/\d/', $password)) {
        $errors[] = 'Password must contain a number.';
    }

    return $errors;
}

function positiveInteger(mixed $value, int $max = PHP_INT_MAX): ?int
{
    $filtered = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => $max],
    ]);

    return $filtered === false ? null : (int) $filtered;
}
