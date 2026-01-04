-- ============================================
-- ERP SYSTEM DATABASE SCHEMA
-- Rathnayake Global Enterprises (Pvt) Ltd.
-- Bites and Sweets Distribution System
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS rathnayake_erp;
USE rathnayake_erp;

-- ============================================
-- 1. SUPPLIERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_supplier_name (supplier_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. PRODUCTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    buying_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    selling_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    stock_quantity INT NOT NULL DEFAULT 0,
    supplier_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL,
    INDEX idx_product_name (product_name),
    INDEX idx_stock (stock_quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. ROUTES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS routes (
    route_id INT AUTO_INCREMENT PRIMARY KEY,
    route_name VARCHAR(255) NOT NULL,
    route_description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_route_name (route_name),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. SELLERS/SHOPS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS sellers (
    seller_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_name VARCHAR(255) NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20),
    address TEXT,
    route_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE RESTRICT,
    INDEX idx_shop_name (shop_name),
    INDEX idx_route (route_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. SALES TRANSACTIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    route_id INT NOT NULL,
    sale_date DATE NOT NULL,
    total_revenue DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    total_profit DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(100),
    FOREIGN KEY (seller_id) REFERENCES sellers(seller_id) ON DELETE RESTRICT,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE RESTRICT,
    INDEX idx_sale_date (sale_date),
    INDEX idx_seller (seller_id),
    INDEX idx_route (route_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. SALES ITEMS TABLE (Details)
-- ============================================
CREATE TABLE IF NOT EXISTS sales_items (
    sale_item_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    buying_price DECIMAL(10, 2) NOT NULL,
    selling_price DECIMAL(10, 2) NOT NULL,
    revenue DECIMAL(10, 2) NOT NULL,
    profit DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT,
    INDEX idx_sale (sale_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. USERS TABLE (Optional - for authentication)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'sales_rep', 'manager') DEFAULT 'sales_rep',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA FOR TESTING
-- ============================================

-- Insert Sample Suppliers
INSERT INTO suppliers (supplier_name, contact_number, address) VALUES
('Ceylon Sweets Ltd', '0112345678', 'No. 123, Main Street, Colombo 07'),
('Lanka Bites Co.', '0117654321', 'No. 456, Galle Road, Dehiwala'),
('Sweet Treats International', '0118889999', 'No. 789, Kandy Road, Kadawatha');

-- Insert Sample Routes
INSERT INTO routes (route_name, route_description) VALUES
('Colombo Route', 'Covers Colombo 1-15 areas'),
('Gampaha Route', 'Covers Gampaha district'),
('Kandy Route', 'Covers Kandy and surrounding areas'),
('Negombo Route', 'Covers Negombo and coastal areas');

-- Insert Sample Products
INSERT INTO products (product_name, buying_price, selling_price, stock_quantity, supplier_id) VALUES
('Chocolate Cake (500g)', 350.00, 450.00, 100, 1),
('Vanilla Bites (250g)', 180.00, 230.00, 200, 2),
('Coconut Cookies (200g)', 120.00, 160.00, 300, 1),
('Butter Cake (1kg)', 680.00, 850.00, 50, 1),
('Mixed Nuts Pack (150g)', 250.00, 320.00, 150, 3),
('Fruit Cake (500g)', 420.00, 550.00, 80, 1),
('Cashew Cookies (200g)', 280.00, 360.00, 120, 2),
('Brownie Pack (300g)', 320.00, 420.00, 90, 3);

-- Insert Sample Sellers
INSERT INTO sellers (shop_name, owner_name, contact_number, address, route_id) VALUES
('Happy Mart', 'Kamal Perera', '0771234567', 'No. 45, Galle Road, Colombo 03', 1),
('City Groceries', 'Nimal Silva', '0772345678', 'No. 78, Main Street, Colombo 07', 1),
('Super Store Gampaha', 'Sunil Fernando', '0773456789', 'No. 12, Colombo Road, Gampaha', 2),
('Kandy Food Mart', 'Ajith Bandara', '0774567890', 'No. 34, Peradeniya Road, Kandy', 3),
('Quick Shop', 'Chaminda Dias', '0775678901', 'No. 56, Station Road, Gampaha', 2),
('Corner Store', 'Priyantha Kumar', '0776789012', 'No. 89, Temple Road, Colombo 10', 1),
('Beach Mart', 'Roshan Jayasinghe', '0777890123', 'No. 23, Beach Road, Negombo', 4);

-- Insert Sample User (password: admin123)
INSERT INTO users (username, password_hash, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- ============================================
-- USEFUL VIEWS FOR REPORTING
-- ============================================

-- View: Daily Sales Summary
CREATE OR REPLACE VIEW daily_sales_summary AS
SELECT 
    s.sale_date,
    COUNT(DISTINCT s.sale_id) as total_transactions,
    SUM(s.total_revenue) as total_revenue,
    SUM(s.total_profit) as total_profit,
    AVG(s.total_profit) as avg_profit_per_sale
FROM sales s
GROUP BY s.sale_date
ORDER BY s.sale_date DESC;

-- View: Monthly Sales Summary
CREATE OR REPLACE VIEW monthly_sales_summary AS
SELECT 
    DATE_FORMAT(s.sale_date, '%Y-%m') as month,
    YEAR(s.sale_date) as year,
    MONTHNAME(s.sale_date) as month_name,
    COUNT(DISTINCT s.sale_id) as total_transactions,
    SUM(s.total_revenue) as total_revenue,
    SUM(s.total_profit) as total_profit,
    AVG(s.total_profit) as avg_profit_per_sale
FROM sales s
GROUP BY DATE_FORMAT(s.sale_date, '%Y-%m'), YEAR(s.sale_date), MONTHNAME(s.sale_date)
ORDER BY DATE_FORMAT(s.sale_date, '%Y-%m') DESC;

-- View: Route Performance
CREATE OR REPLACE VIEW route_performance AS
SELECT 
    r.route_id,
    r.route_name,
    COUNT(DISTINCT s.sale_id) as total_sales,
    COUNT(DISTINCT sel.seller_id) as active_sellers,
    SUM(s.total_revenue) as total_revenue,
    SUM(s.total_profit) as total_profit,
    AVG(s.total_profit) as avg_profit_per_sale
FROM routes r
LEFT JOIN sales s ON r.route_id = s.route_id
LEFT JOIN sellers sel ON r.route_id = sel.route_id AND sel.is_active = 1
GROUP BY r.route_id, r.route_name
ORDER BY total_profit DESC;

-- View: Product Performance
CREATE OR REPLACE VIEW product_performance AS
SELECT 
    p.product_id,
    p.product_name,
    p.stock_quantity,
    COUNT(si.sale_item_id) as times_sold,
    SUM(si.quantity) as total_quantity_sold,
    SUM(si.revenue) as total_revenue,
    SUM(si.profit) as total_profit
FROM products p
LEFT JOIN sales_items si ON p.product_id = si.product_id
GROUP BY p.product_id, p.product_name, p.stock_quantity
ORDER BY total_profit DESC;

-- View: Seller Performance
CREATE OR REPLACE VIEW seller_performance AS
SELECT 
    sel.seller_id,
    sel.shop_name,
    sel.owner_name,
    r.route_name,
    COUNT(s.sale_id) as total_purchases,
    SUM(s.total_revenue) as total_revenue,
    SUM(s.total_profit) as total_profit,
    MAX(s.sale_date) as last_purchase_date
FROM sellers sel
LEFT JOIN sales s ON sel.seller_id = s.seller_id
LEFT JOIN routes r ON sel.route_id = r.route_id
WHERE sel.is_active = 1
GROUP BY sel.seller_id, sel.shop_name, sel.owner_name, r.route_name
ORDER BY total_profit DESC;

-- ============================================
-- END OF SCHEMA
-- ============================================
