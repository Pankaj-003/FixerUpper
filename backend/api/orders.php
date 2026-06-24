<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../middleware/authMiddleware.php';

requireMethod('GET');
$authenticatedUser = requireAuth();

$limit = filter_var($_GET['limit'] ?? 25, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 100],
]);
$limit = $limit === false ? 25 : (int) $limit;

$orderStatement = database()->prepare(
    'SELECT id, order_number, total_amount, status, shipping_name, shipping_city, created_at
     FROM orders
     WHERE user_id = :user_id
     ORDER BY created_at DESC
     LIMIT :limit'
);
$orderStatement->bindValue(':user_id', $authenticatedUser['id'], PDO::PARAM_INT);
$orderStatement->bindValue(':limit', $limit, PDO::PARAM_INT);
$orderStatement->execute();
$orders = $orderStatement->fetchAll();

if ($orders !== []) {
    $orderIds = array_map(static fn (array $order): int => (int) $order['id'], $orders);
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $itemStatement = database()->prepare(
        "SELECT order_id, product_id, product_name, quantity, unit_price, line_total
         FROM order_items
         WHERE order_id IN ({$placeholders})
         ORDER BY id ASC"
    );
    $itemStatement->execute($orderIds);

    $itemsByOrder = [];
    foreach ($itemStatement->fetchAll() as $item) {
        $itemsByOrder[(int) $item['order_id']][] = [
            'product_id' => (int) $item['product_id'],
            'product_name' => e((string) $item['product_name']),
            'quantity' => (int) $item['quantity'],
            'unit_price' => number_format((float) $item['unit_price'], 2, '.', ''),
            'line_total' => number_format((float) $item['line_total'], 2, '.', ''),
        ];
    }
} else {
    $itemsByOrder = [];
}

$safeOrders = array_map(static fn (array $order): array => [
    'id' => (int) $order['id'],
    'order_number' => e((string) $order['order_number']),
    'total_amount' => number_format((float) $order['total_amount'], 2, '.', ''),
    'status' => e((string) $order['status']),
    'shipping_name' => e((string) $order['shipping_name']),
    'shipping_city' => e((string) $order['shipping_city']),
    'created_at' => e((string) $order['created_at']),
    'items' => $itemsByOrder[(int) $order['id']] ?? [],
], $orders);

successResponse([
    'authenticated' => true,
    'orders' => $safeOrders,
], 'Orders retrieved.');
