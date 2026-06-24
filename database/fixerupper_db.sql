CREATE DATABASE IF NOT EXISTS fixerupper_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fixerupper_db;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL UNIQUE,
    description VARCHAR(500) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image_url VARCHAR(255) NOT NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CHECK (price >= 0),
    CHECK (stock >= 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(32) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('confirmed', 'processing', 'shipped', 'completed', 'cancelled')
        NOT NULL DEFAULT 'confirmed',
    shipping_name VARCHAR(100) NOT NULL,
    shipping_email VARCHAR(190) NOT NULL,
    shipping_phone VARCHAR(30) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_postal_code VARCHAR(20) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_orders_user_created (user_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    line_total DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_order_items_order (order_id),
    CHECK (quantity > 0),
    CHECK (unit_price >= 0),
    CHECK (line_total >= 0)
) ENGINE=InnoDB;

INSERT INTO products (name, slug, description, price, stock, image_url, featured)
VALUES
    ('Cordless Drill', 'cordless-drill', '18V cordless drill with variable speed, LED work light, and two rechargeable batteries.', 89.99, 25, 'images/cordless-drill.svg', 1),
    ('Hammer', 'hammer', 'Balanced 16 oz forged-steel claw hammer with a shock-absorbing grip.', 24.50, 60, 'images/hammer.svg', 1),
    ('Screwdriver Set', 'screwdriver-set', 'Twelve-piece magnetic screwdriver set with Phillips, flat, and precision tips.', 32.99, 45, 'images/screwdriver-set.svg', 1),
    ('Tool Box', 'tool-box', 'Rugged 20-inch tool box with removable tray, metal latches, and padlock eye.', 41.75, 30, 'images/tool-box.svg', 1),
    ('Electric Saw', 'electric-saw', 'Powerful 15A circular saw with a 7-1/4 inch blade and adjustable bevel.', 119.00, 18, 'images/electric-saw.svg', 0),
    ('Paint Sprayer', 'paint-sprayer', 'High-volume low-pressure sprayer with three patterns for smooth indoor and outdoor finishes.', 74.95, 20, 'images/paint-sprayer.svg', 0),
    ('Ladder', 'ladder', 'Multi-position aluminum ladder rated for 300 lb with secure locking hinges.', 159.99, 12, 'images/ladder.svg', 0),
    ('Wrench Set', 'wrench-set', 'Ten-piece chrome vanadium combination wrench set in a roll-up organizer.', 49.99, 38, 'images/wrench-set.svg', 0)
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    price = VALUES(price),
    stock = VALUES(stock),
    image_url = VALUES(image_url),
    featured = VALUES(featured);
