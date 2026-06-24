<?php

declare(strict_types=1);

function sendJson(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        $payload,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );
    exit;
}

function successResponse(array $data = [], string $message = 'Success', int $statusCode = 200): never
{
    sendJson([
        'success' => true,
        'message' => $message,
        'data' => $data,
    ], $statusCode);
}

function errorResponse(string $message, int $statusCode = 400, array $errors = []): never
{
    $payload = [
        'success' => false,
        'message' => $message,
    ];

    if ($errors !== []) {
        $payload['errors'] = $errors;
    }

    sendJson($payload, $statusCode);
}

function readJsonBody(): array
{
    $rawBody = file_get_contents('php://input');
    if ($rawBody === false || trim($rawBody) === '') {
        return [];
    }

    try {
        $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        errorResponse('The request body must contain valid JSON.', 400);
    }

    if (!is_array($decoded)) {
        errorResponse('The JSON request body must be an object.', 400);
    }

    return $decoded;
}

function requireMethod(string ...$allowedMethods): void
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $allowedMethods = array_map('strtoupper', $allowedMethods);

    if (!in_array($method, $allowedMethods, true)) {
        header('Allow: ' . implode(', ', $allowedMethods));
        errorResponse('Method not allowed.', 405);
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
