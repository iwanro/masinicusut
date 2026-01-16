-- =====================================================
-- Taxe Transport & Email Settings
-- VERSIUNE CPANEL - Fără CREATE DATABASE
-- =====================================================
--
-- IMPORTANT:
-- 1. Creează baza de date din cPanel înainte
-- 2. Importă database_cpanel.sql întâi
-- 3. Apoi importă acest fișier
-- =====================================================

-- =====================================================
-- 1. Tabel taxe transport
-- =====================================================
CREATE TABLE IF NOT EXISTS shipping_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    county VARCHAR(50) NOT NULL,
    city VARCHAR(50) DEFAULT NULL,
    shipping_cost DECIMAL(10, 2) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_location (county, city),
    INDEX idx_county (county)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. Taxe default pentru județe
-- =====================================================

-- București și împrejurimi
INSERT INTO shipping_rates (county, shipping_cost) VALUES
('București', 15.00),
('Ilfov', 15.00);

-- Județe mari - centre universitare
INSERT INTO shipping_rates (county, shipping_cost) VALUES
('Cluj', 20.00),
('Timiș', 20.00),
('Brașov', 20.00),
('Constanța', 20.00),
('Iași', 20.00),
('Sibiu', 20.00),
('Bihor', 20.00);

-- Județe - Nord și Est
INSERT INTO shipping_rates (county, shipping_cost) VALUES
('Maramureș', 25.00),
('Suceava', 25.00),
('Botoșani', 25.00),
('Neamț', 25.00),
('Bacău', 25.00),
('Vaslui', 25.00),
('Galați', 25.00),
('Vrancea', 25.00);

-- Județe - Vest și Sud-Vest
INSERT INTO shipping_rates (county, shipping_cost) VALUES
('Arad', 20.00),
('Caraș-Severin', 25.00),
('Mehedinți', 25.00),
('Gorj', 25.00),
('Dolj', 25.00),
('Olt', 25.00),
('Teleorman', 25.00);

-- Județe - Sud și Centru
INSERT INTO shipping_rates (county, shipping_cost) VALUES
('Giurgiu', 25.00),
('Călărași', 25.00),
('Ialomița', 25.00),
('Brăila', 25.00),
('Buzău', 25.00),
('Prahova', 20.00),
('Dâmbovița', 20.00),
('Argeș', 20.00),
('Vâlcea', 25.00),
('Hunedoara', 25.00);

-- Județe - Centru și Nord
INSERT INTO shipping_rates (county, shipping_cost) VALUES
('Alba', 20.00),
('Mureș', 20.00),
('Harghita', 25.00),
('Covasna', 25.00),
('Bistrița-Năsăud', 25.00),
('Sălaj', 25.00),
('Satu Mare', 25.00),
('Tulcea', 25.00);

-- =====================================================
-- 3. Setări email în settings table
-- =====================================================
INSERT INTO settings (`key`, value, type, description) VALUES
('smtp_host', 'smtp.hostico.ro', 'text', 'SMTP Host'),
('smtp_port', '587', 'number', 'SMTP Port'),
('smtp_username', '', 'text', 'SMTP Username'),
('smtp_password', '', 'text', 'SMTP Password'),
('smtp_encryption', 'tls', 'text', 'SMTP Encryption (tls/ssl/none)'),
('admin_email', 'admin@sundari.ro', 'text', 'Email pentru notificări comenzi'),
('email_from_name', 'SUNDARI TOP STAR', 'text', 'Nume expeditor email'),
('email_from_address', 'noreply@sundari.ro', 'text', 'Adresă expeditor email'),
('email_orders_enabled', '0', 'boolean', 'Activează notificări email comenzi')
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- =====================================================
-- Finalizat cu succes!
-- =====================================================
