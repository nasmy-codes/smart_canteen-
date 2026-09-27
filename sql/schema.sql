-- ============================================================
-- Smart Canteen - Database Schema
-- Import this file in phpMyAdmin (or `mysql -u root -p < schema.sql`)
-- before running the application.
-- ============================================================

CREATE DATABASE IF NOT EXISTS smart_canteen
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE smart_canteen;

-- ------------------------------------------------------------
-- Users (customers + admins share one table, split by `role`)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,          -- stored with PHP password_hash()
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Food items (managed by admin)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS food_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT '',
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'Main',
    image VARCHAR(255) DEFAULT '',
    status ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Orders (one row per placed order)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE,   -- e.g. ORD2026001
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('Pending', 'Preparing', 'Ready for Pickup', 'Completed', 'Cancelled')
        NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Order items (line items per order - keeps a snapshot of
-- name/price at the time of order, so later menu edits don't
-- change historical order records)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_item_id INT DEFAULT NULL,
    item_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Sample data
-- ------------------------------------------------------------

-- Admin login  -> username: admin      password: admin123
-- Demo login   -> username: customer   password: customer123
INSERT INTO users (full_name, username, email, password, role) VALUES
('System Admin', 'admin', 'admin@smartcanteen.com',
 '$2y$10$kRcT0i8uzrrcS6T9gehwoOCfbYPlaiDnvfPW/no5f2ulTZA9bEtP2', 'admin'),
('Demo Customer', 'customer', 'customer@smartcanteen.com',
 '$2y$10$Nq5gViT4kcfGoB6mLQO3dODiKM/VzP/ntF2hI7ht4SteURyLrSQpa', 'customer');

INSERT INTO food_items (name, description, price, category, image, status) VALUES
('Chicken Rice', 'Steamed rice served with grilled chicken and gravy', 250.00, 'Main Meals', 'chicken-rice.jpg', 'available'),
('Chicken Kottu', 'Chopped roti stir-fried with chicken, egg and vegetables', 250.00, 'Main Meals', 'kottu.jpg', 'available'),
('Fried Rice', 'Wok-fried rice with mixed vegetables and egg', 300.00, 'Main Meals', 'fried-rice.jpg', 'available'),
('Egg Rice', 'Fried rice topped with a fried egg', 220.00, 'Main Meals', 'egg-rice.jpg', 'available'),
('Rolls', 'Crispy vegetable roll', 80.00, 'Short Eats', 'rolls.jpg', 'available'),
('Cutlet', 'Fish cutlet, deep fried', 40.00, 'Short Eats', 'cutlet.jpg', 'available'),
('Samosa', 'Spiced vegetable samosa', 50.00, 'Short Eats', 'samosa.jpg', 'available'),
('Coca Cola', 'Chilled 400ml bottle', 120.00, 'Beverages', 'coca-cola.jpg', 'available'),
('Fresh Juice', 'Seasonal fresh fruit juice', 150.00, 'Beverages', 'fresh-juice.jpg', 'available'),
('Water Bottle', '500ml mineral water', 60.00, 'Beverages', 'water-bottle.jpg', 'available');
