-- =====================================================
-- Migration: Taxe Transport & Email Notifications
-- SUNDARI TOP STAR S.R.L.
-- Data: 16 Ianuarie 2026
-- =====================================================

-- 1. Tabel taxe transport per judet/localitate
CREATE TABLE IF NOT EXISTS shipping_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    county VARCHAR(50) NOT NULL,
    city VARCHAR(50) DEFAULT NULL,
    shipping_cost DECIMAL(10, 2) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_location (county, city),
    INDEX idx_county (county),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Taxe transport per judet/localitate';

-- 2. Insert taxe default pentru judete principale
INSERT INTO shipping_rates (county, shipping_cost) VALUES
('București', 15.00),
('Alba', 20.00),
('Arad', 20.00),
('Argeș', 20.00),
('Bacău', 20.00),
('Bihor', 20.00),
('Bistrița-Năsăud', 20.00),
('Botoșani', 20.00),
('Brașov', 20.00),
('Brăila', 20.00),
('Buzău', 20.00),
('Caraș-Severin', 20.00),
('Călărași', 20.00),
('Cluj', 20.00),
('Constanța', 25.00),
('Covasna', 20.00),
('Dâmbovița', 15.00),
('Dolj', 20.00),
('Galați', 20.00),
('Gorj', 20.00),
('Harghita', 20.00),
('Hunedoara', 20.00),
('Ialomița', 20.00),
('Iași', 25.00),
('Ilfov', 15.00),
('Maramureș', 20.00),
('Mehedinți', 20.00),
('Mureș', 20.00),
('Neamț', 20.00),
('Olt', 20.00),
('Prahova', 15.00),
('Sălaj', 20.00),
('Satu Mare', 20.00),
('Sibiu', 20.00),
('Suceava', 20.00),
('Teleorman', 20.00),
('Timiș', 20.00),
('Tulcea', 25.00),
('Vaslui', 20.00),
('Vâlcea', 20.00),
('Vrancea', 20.00);

-- 3. Setari email in settings table
INSERT INTO settings (`key`, value, type, description) VALUES
('smtp_host', 'localhost', 'text', 'SMTP Host'),
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
-- SFARSIT MIGRATION
-- =====================================================
