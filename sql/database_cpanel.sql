-- =====================================================
-- SUNDARI TOP STAR S.R.L. - E-Commerce Database Schema
-- Piese Mașini de Cusut
-- VERSIUNE CPANEL - Fără CREATE DATABASE
-- =====================================================
--
-- IMPORTANT: Creează baza de date din cPanel înainte de import!
-- 1. cPanel → MySQL Databases → Create New Database
-- 2. Importă acest fișier în phpMyAdmin selectând baza de date creată
-- =====================================================

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
    price DECIMAL(10, 2) NOT NULL,
    compare_price DECIMAL(10, 2),
    sku VARCHAR(100),
    stock INT DEFAULT 0,
    image VARCHAR(255),
    images JSON,
    category_id INT,
    brand_id INT,
    is_active TINYINT(1) DEFAULT 1,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (brand_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_brand (brand_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. CART - Coș de cumpărături
-- =====================================================
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. ORDERS - Comenzi
-- =====================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_name VARCHAR(100),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    shipping_city VARCHAR(50),
    shipping_county VARCHAR(50),
    shipping_postal_code VARCHAR(10),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. ORDER_ITEMS - Detalii comandă
-- =====================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. ANALYTICS - Statistici vizite
-- =====================================================
CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_date DATE NOT NULL,
    page_views INT DEFAULT 0,
    unique_visitors INT DEFAULT 0,
    orders_count INT DEFAULT 0,
    revenue DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (event_date),
    INDEX idx_date (event_date)
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
-- INSERT DATE INITIALE
-- =====================================================

-- Admin user (parola: admin123)
INSERT INTO settings (`key`, value, type, description) VALUES
('site_name', 'SUNDARI TOP STAR', 'text', 'Numele site-ului'),
('site_description', 'Piese Mașini de Cusut', 'text', 'Descrierea site-ului'),
('contact_email', 'contact@sundari.ro', 'text', 'Email de contact'),
('contact_phone', '07xx xxx xxx', 'text', 'Telefon de contact'),
('shipping_cost', '15', 'number', 'Taxă de transport default (RON)'),
('free_shipping_threshold', '200', 'number', 'Prag pentru transport gratuit (RON)'),
('currency', 'RON', 'text', 'Moneda');

-- Admin user (parola: admin123)
INSERT INTO users (email, password_hash, name, role) VALUES
('admin@sundari.ro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin');

-- Branduri
INSERT INTO categories (name, slug, type, sort_order) VALUES
('Singer', 'singer', 'brand', 1),
('Pfaff', 'pfaff', 'brand', 2),
('Brother', 'brother', 'brand', 3),
('Janome', 'janome', 'brand', 4),
('Baby Lock', 'baby-lock', 'brand', 5),
('Juki', 'juki', 'brand', 6);

-- Tipuri produse pentru Singer
INSERT INTO categories (name, slug, description, parent_id, type, sort_order) VALUES
('Ace', 'ace', 'Ace pentru mașini de cusut Singer', 1, 'product_type', 1),
('Mingi de Cauciuc', 'mingi-de-cauciuc', 'Mingi de cauciuc originale', 1, 'product_type', 2),
('Plăci de Cusut', 'placi-de-cusut', 'Plăci de cusut și accesorii', 1, 'product_type', 3),
('Carcurele', 'carcurele', 'Carcurele și accesorii transmisie', 1, 'product_type', 4),
('Cleme', 'cleme', 'Cleme și fixări', 1, 'product_type', 5);

-- Produse exemplu
INSERT INTO products (name, slug, description, price, sku, stock, category_id, brand_id, is_active, featured) VALUES
('Ace Singer 130/705H - Set 5 buc', 'ace-singer-130705h-set-5-buc',
'Ace universale Singer 130/705H, potrivite pentru majoritatea modelelor. Set 5 bucăți.', 25.00, 'SNG-NEEDLE-001', 50, 7, 1, 1, 1),

('Mingi de Cauciuc Singer - Mărime Medie', 'mingi-de-cauciuc-singer-marime-medie',
'Mingi de cauciuc originale Singer, mărime medie. Potrivite pentru modelele Tradition, Promise, Simple.', 35.00, 'SNG-BOB-MED', 30, 8, 1, 1, 1),

('Placă de Cusut Singer Universală', 'placa-de-cusut-singer-universala',
'Placă de cusut universală Singer, din metal, cu rafturi laterale pentru accesorii.', 120.00, 'SNG-TABLE-UNI', 15, 9, 1, 1, 1),

('Carcurel Singer Original - 1.5cm', 'carcurel-singer-original-15cm',
'Carcurel original Singer, dimensiune 1.5cm, potrivit pentru modelele cu tensionare automată.', 45.00, 'SNG-TENSION-15', 25, 10, 1, 1, 1),

('Set Cleme Fixare Placă Singer', 'set-cleme-fixare-placa-singer',
'Set complet cleme pentru fixarea plăcii de cusut la modelele Singer. Include șuruburi.', 30.00, 'SNG-CLAMP-SET', 40, 11, 1, 1, 0);

-- Produse pentru alte branduri
INSERT INTO products (name, slug, description, price, sku, stock, category_id, brand_id, is_active, featured) VALUES
('Ace Pfaff 130/705H - Calitate Premium', 'ace-pfaff-130705h-calitate-premium',
'Ace Pfaff 130/705H, calitate premium, potrivite pentru toate modelele Pfaff domestice.', 30.00, 'PF-NEEDLE-001', 45, 7, 2, 1, 1),

('Mingi de Cauciuc Brother - Standard', 'mingi-de-cauciuc-brother-standard',
'Mingi de cauciuc Brother standard, potrivite pentru seria CS, LS, NV.', 32.00, 'BRO-BOB-STD', 35, 8, 3, 1, 1),

('Set Ace Janome - Asortate', 'set-ace-janome-asortate',
'Set 10 ace Janome asortate, diferite mărimi pentru diverse tipuri de țesături.', 40.00, 'JAN-NEEDLE-SET', 20, 7, 4, 1, 0);
