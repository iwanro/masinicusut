-- =====================================================
-- SUNDARI TOP STAR S.R.L. - E-Commerce Database Schema
-- Piese Mașini de Cusut
-- =====================================================

-- Creare bază de date
CREATE DATABASE IF NOT EXISTS piese_masini_cusut CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE piese_masini_cusut;

-- =====================================================
-- 1. USERS - Utilizatori
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    county VARCHAR(50),
    postal_code VARCHAR(10),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. CATEGORIES - Categorii (Mărci + Tipuri produse)
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    parent_id INT DEFAULT NULL,
    type ENUM('brand', 'product_type') NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. PRODUCTS - Produse
-- =====================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    sku VARCHAR(50) UNIQUE,
    category_id INT, -- Marca
    subcategory_id INT, -- Tip produs
    image VARCHAR(255),
    images JSON, -- Array de imagini suplimentare
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    meta_title VARCHAR(200),
    meta_description VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (subcategory_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_subcategory (subcategory_id),
    INDEX idx_sku (sku),
    INDEX idx_is_active (is_active),
    FULLTEXT idx_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. CART - Coș cumpărături
-- =====================================================
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(100),
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_session (session_id),
    UNIQUE KEY unique_product (user_id, session_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. ORDERS - Comenzi
-- =====================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_name VARCHAR(100) NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(50) NOT NULL,
    shipping_county VARCHAR(50),
    shipping_postal_code VARCHAR(10),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. ORDER_ITEMS - Detalii comandă
-- =====================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. ANALYTICS - Statistici și vizite
-- =====================================================
CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    page_views INT DEFAULT 0,
    unique_visitors INT DEFAULT 0,
    orders_count INT DEFAULT 0,
    revenue DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. SETTINGS - Setări site
-- =====================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATE DE START - Insert date inițiale
-- =====================================================

-- Insert admin user (password: admin123)
INSERT INTO users (email, password_hash, name, role) VALUES
('admin@sundari.ro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin');

-- Insert setări default
INSERT INTO settings (`key`, value, type, description) VALUES
('site_name', 'SUNDARI TOP STAR - Piese Mașini de Cusut', 'text', 'Numele site-ului'),
('site_description', 'Piese, accesorii și consumabile pentru mașini de cusut', 'text', 'Descrierea site-ului'),
('contact_email', 'contact@sundari.ro', 'text', 'Email de contact'),
('contact_phone', '+40 700 000 000', 'text', 'Telefon de contact'),
('currency', 'RON', 'text', 'Moneda folosită'),
('tax_rate', '19', 'number', 'TVA (%)'),
('free_shipping_threshold', '200', 'number', 'Liber transport peste (RON)'),
('shipping_cost', '15', 'number', 'Cost transport (RON)');

-- Insert categorii de bază (Mărci)
INSERT INTO categories (name, slug, type, sort_order) VALUES
('Singer', 'singer', 'brand', 1),
('Brother', 'brother', 'brand', 2),
('Pfaff', 'pfaff', 'brand', 3),
('Janome', 'janome', 'brand', 4),
('Bernina', 'bernina', 'brand', 5),
('Juki', 'juki', 'brand', 6),
('Universal', 'universal', 'brand', 7);

-- Insert subcategorii (Tipuri produse)
INSERT INTO categories (name, slug, type, parent_id, sort_order) VALUES
('Ace', 'ace', 'product_type', NULL, 1),
('Cărlige', 'clichete', 'product_type', NULL, 2),
('Cremăiere', 'cremiere', 'product_type', NULL, 3),
('Suport bobină', 'suport-bobina', 'product_type', NULL, 4),
('Pedală', 'pedala', 'product_type', NULL, 5),
('Motor', 'motor', 'product_type', NULL, 6),
('Accesorii', 'accesorii', 'product_type', NULL, 7),
('Consumabile', 'consumabile', 'product_type', NULL, 8);

-- Insert câteva produse de exemplu
INSERT INTO products (name, slug, description, short_description, price, stock, sku, category_id, subcategory_id, is_featured) VALUES
('Set 5 ace Singer universal', 'set-5-ace-singer-universal', 'Set de 5 ace pentru mașini de cusut Singer, compatibil cu majoritatea modelelor.', 'Set 5 ace compatibile Singer', 25.00, 100, 'SNG-ACE-001', 1, 1, 1),
('Cârlig Brother original', 'clichet-brother-original', 'Cârlig original Brother, potrivit pentru modelele seriei CS.', 'Cârlig original Brother', 45.00, 50, 'BRO-CL-001', 2, 2, 1),
('Cremăieră Pfaff metal', 'cremiere-pfaff-metal', 'Cremăieră metalică pentru mașini Pfaff, construcție robustă.', 'Cremăieră metalică Pfaff', 85.00, 30, 'PF-CR-001', 3, 3, 0),
('Suport bobină Janome', 'suport-bobina-janome', 'Suport bobină pentru mașini Janome, design ergonomic.', 'Suport bobină Janome', 35.00, 75, 'JAN-SB-001', 4, 4, 0),
('Pedală Universal control viteză', 'pedala-universal-control-viteza', 'Pedală universală cu control electronic al vitezei, compatibilă cu majoritatea mașinilor.', 'Pedală universală electronică', 65.00, 40, 'UNI-PED-001', 7, 5, 1);

-- =====================================================
-- PROCEDURI ȘI TRIGGERE (opțional)
-- =====================================================

-- Trigger pentru actualizare stoc la plasare comandă
DELIMITER //
CREATE TRIGGER update_stock_on_order
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE products
    SET stock = stock - NEW.quantity
    WHERE id = NEW.product_id;
END//
DELIMITER ;

-- Trigger pentru actualizare analytics la comandă nouă
DELIMITER //
CREATE TRIGGER update_analytics_on_order
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    INSERT INTO analytics (date, orders_count, revenue)
    VALUES (CURDATE(), 1, NEW.total_amount)
    ON DUPLICATE KEY UPDATE
        orders_count = orders_count + 1,
        revenue = revenue + NEW.total_amount;
END//
DELIMITER ;

-- =====================================================
-- SFÂRȘIT
-- =====================================================
