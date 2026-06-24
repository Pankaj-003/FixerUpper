<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireMethod('POST');
$input = readJsonBody();

$name = cleanText($input['name'] ?? '', 100);
$email = cleanEmail($input['email'] ?? '');
$password = $input['password'] ?? null;
$errors = [];

if (mb_strlen($name, 'UTF-8') < 2) {
    $errors['name'] = 'Name must be at least 2 characters.';
}
if (!isValidEmail($email)) {
    $errors['email'] = 'Enter a valid email address.';
}

$passwordErrors = validatePassword($password);
if ($passwordErrors !== []) {
    $errors['password'] = implode(' ', $passwordErrors);
}

if ($errors !== []) {
    errorResponse('Please correct the highlighted fields.', 422, $errors);
}

$passwordHash = password_hash((string) $password, PASSWORD_DEFAULT);
if ($passwordHash === false) {
    throw new RuntimeException('Unable to secure the password.');
}

try {
    $statement = database()->prepare(
        'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)'
    );
    $statement->execute([
        ':name' => $name,
        ':email' => $email,
        ':password_hash' => $passwordHash,
    ]);
} catch (PDOException $exception) {
    if ((string) $exception->getCode() === '23000') {
        errorResponse('An account with this email address already exists.', 409);
    }
    throw $exception;
}

successResponse([
    'user' => [
        'id' => (int) database()->lastInsertId(),
        'name' => e($name),
        'email' => e($email),
    ],
], 'Registration successful. You can now log in.', 201);
