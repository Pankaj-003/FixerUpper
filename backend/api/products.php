<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireMethod('GET');

$featuredOnly = filter_var($_GET['featured'] ?? false, FILTER_VALIDATE_BOOL);
$limit = filter_var($_GET['limit'] ?? 50, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 100],
]);
$limit = $limit === false ? 50 : (int) $limit;

$sql = 'SELECT id, name, slug, description, price, stock, image_url, featured
        FROM products';
if ($featuredOnly) {
    $sql .= ' WHERE featured = :featured';
}
$sql .= ' ORDER BY featured DESC, id ASC LIMIT :limit';

$statement = database()->prepare($sql);
if ($featuredOnly) {
    $statement->bindValue(':featured', 1, PDO::PARAM_INT);
}
$statement->bindValue(':limit', $limit, PDO::PARAM_INT);
$statement->execute();

$products = array_map(static fn (array $product): array => [
    'id' => (int) $product['id'],
    'name' => e((string) $product['name']),
    'slug' => e((string) $product['slug']),
    'description' => e((string) $product['description']),
    'price' => number_format((float) $product['price'], 2, '.', ''),
    'stock' => (int) $product['stock'],
    'image_url' => e((string) $product['image_url']),
    'featured' => (bool) $product['featured'],
], $statement->fetchAll());

successResponse(['products' => $products], 'Products retrieved.');
