<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireMethod('POST');
$input = readJsonBody();

$email = cleanEmail($input['email'] ?? '');
$password = $input['password'] ?? null;

if (!isValidEmail($email) || !is_string($password) || $password === '') {
    errorResponse('Email and password are required.', 422);
}

$statement = database()->prepare(
    'SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1'
);
$statement->execute([':email' => $email]);
$user = $statement->fetch();

$hashToCheck = is_array($user)
    ? (string) $user['password_hash']
    : '$2y$10$wH2QFPqXfSEbmTCf7U9XheWfjO9lM3JGTsNQMyiz3P2P1xRr8fD8K';

if (!password_verify($password, $hashToCheck) || !is_array($user)) {
    errorResponse('The email address or password is incorrect.', 401);
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['logged_in_at'] = time();

$token = createJwt((int) $user['id'], (string) $user['email']);
setAuthCookie($token);

successResponse([
    'user' => [
        'id' => (int) $user['id'],
        'name' => e((string) $user['name']),
        'email' => e((string) $user['email']),
    ],
], 'Login successful.');
