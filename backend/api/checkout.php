<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../middleware/authMiddleware.php';

requireMethod('POST');
$authenticatedUser = requireAuth();
$input = readJsonBody();

$items = $input['items'] ?? null;
$shipping = is_array($input['shipping'] ?? null) ? $input['shipping'] : [];

if (!is_array($items) || $items === [] || count($items) > 50) {
    errorResponse('Your cart must contain between 1 and 50 products.', 422);
}

$shippingData = [
    'name' => cleanText($shipping['name'] ?? '', 100),
    'email' => cleanEmail($shipping['email'] ?? ''),
    'phone' => cleanText($shipping['phone'] ?? '', 30),
    'address' => cleanText($shipping['address'] ?? '', 255),
    'city' => cleanText($shipping['city'] ?? '', 100),
    'postal_code' => strtoupper(cleanText($shipping['postal_code'] ?? '', 20)),
];

$shippingErrors = [];
foreach (['name', 'phone', 'address', 'city', 'postal_code'] as $field) {
    if ($shippingData[$field] === '') {
        $shippingErrors[$field] = 'This field is required.';
    }
}
if (!isValidEmail($shippingData['email'])) {
    $shippingErrors['email'] = 'Enter a valid email address.';
}
if ($shippingErrors !== []) {
    errorResponse('Please provide valid shipping details.', 422, $shippingErrors);
}

$quantities = [];
foreach ($items as $item) {
    if (!is_array($item)) {
        errorResponse('Each cart item must be an object.', 422);
    }

    $productId = positiveInteger($item['product_id'] ?? null);
    $quantity = positiveInteger($item['quantity'] ?? null, 99);
    if ($productId === null || $quantity === null) {
        errorResponse('Every cart item requires a valid product ID and quantity from 1 to 99.', 422);
    }

    $quantities[$productId] = min(99, ($quantities[$productId] ?? 0) + $quantity);
}

$pdo = database();

try {
    $pdo->beginTransaction();

    $productIds = array_keys($quantities);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $productStatement = $pdo->prepare(
        "SELECT id, name, price, stock
         FROM products
         WHERE id IN ({$placeholders})
         FOR UPDATE"
    );
    $productStatement->execute($productIds);
    $products = $productStatement->fetchAll();

    if (count($products) !== count($productIds)) {
        $pdo->rollBack();
        errorResponse('One or more products are no longer available.', 409);
    }

    $orderItems = [];
    $totalCents = 0;
    foreach ($products as $product) {
        $productId = (int) $product['id'];
        $quantity = $quantities[$productId];
        $stock = (int) $product['stock'];

        if ($quantity > $stock) {
            $pdo->rollBack();
            errorResponse(e((string) $product['name']) . " only has {$stock} item(s) in stock.", 409);
        }

        $unitCents = (int) round((float) $product['price'] * 100);
        $lineCents = $unitCents * $quantity;
        $totalCents += $lineCents;
        $orderItems[] = [
            'product_id' => $productId,
            'product_name' => (string) $product['name'],
            'quantity' => $quantity,
            'unit_price' => number_format($unitCents / 100, 2, '.', ''),
            'line_total' => number_format($lineCents / 100, 2, '.', ''),
        ];
    }

    $orderNumber = 'FU-' . gmdate('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    $orderStatement = $pdo->prepare(
        'INSERT INTO orders (
            order_number, user_id, total_amount, status, shipping_name,
            shipping_email, shipping_phone, shipping_address, shipping_city,
            shipping_postal_code
        ) VALUES (
            :order_number, :user_id, :total_amount, :status, :shipping_name,
            :shipping_email, :shipping_phone, :shipping_address, :shipping_city,
            :shipping_postal_code
        )'
    );
    $orderStatement->execute([
        ':order_number' => $orderNumber,
        ':user_id' => $authenticatedUser['id'],
        ':total_amount' => number_format($totalCents / 100, 2, '.', ''),
        ':status' => 'confirmed',
        ':shipping_name' => $shippingData['name'],
        ':shipping_email' => $shippingData['email'],
        ':shipping_phone' => $shippingData['phone'],
        ':shipping_address' => $shippingData['address'],
        ':shipping_city' => $shippingData['city'],
        ':shipping_postal_code' => $shippingData['postal_code'],
    ]);
    $orderId = (int) $pdo->lastInsertId();

    $itemStatement = $pdo->prepare(
        'INSERT INTO order_items (
            order_id, product_id, product_name, quantity, unit_price, line_total
        ) VALUES (
            :order_id, :product_id, :product_name, :quantity, :unit_price, :line_total
        )'
    );
    $stockStatement = $pdo->prepare(
        'UPDATE products
         SET stock = stock - :decrement_quantity
         WHERE id = :product_id AND stock >= :minimum_stock'
    );

    foreach ($orderItems as $orderItem) {
        $itemStatement->execute([
            ':order_id' => $orderId,
            ':product_id' => $orderItem['product_id'],
            ':product_name' => $orderItem['product_name'],
            ':quantity' => $orderItem['quantity'],
            ':unit_price' => $orderItem['unit_price'],
            ':line_total' => $orderItem['line_total'],
        ]);
        $stockStatement->execute([
            ':decrement_quantity' => $orderItem['quantity'],
            ':product_id' => $orderItem['product_id'],
            ':minimum_stock' => $orderItem['quantity'],
        ]);

        if ($stockStatement->rowCount() !== 1) {
            throw new RuntimeException('Stock changed while the order was being placed.');
        }
    }

    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $exception;
}

successResponse([
    'order' => [
        'id' => $orderId,
        'order_number' => e($orderNumber),
        'total_amount' => number_format($totalCents / 100, 2, '.', ''),
        'status' => 'confirmed',
    ],
], 'Your order has been confirmed.', 201);
