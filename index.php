<?php
/**
 * Homepage / Dashboard
 * Rathnayake Global Enterprises ERP System
 * 
 * Main dashboard with overview of business performance
 */

require_once 'db_connection.php';

// Get today's statistics
$today = date('Y-m-d');

$today_stats_query = "SELECT 
                        COUNT(DISTINCT sale_id) as total_sales,
                        SUM(total_revenue) as total_revenue,
                        SUM(total_profit) as total_profit
                      FROM sales
                      WHERE sale_date = '$today'";

$today_stats = fetch_one($today_stats_query);

// Get this month's statistics
$this_month = date('Y-m');

$month_stats_query = "SELECT 
                        COUNT(DISTINCT sale_id) as total_sales,
                        SUM(total_revenue) as total_revenue,
                        SUM(total_profit) as total_profit
                      FROM sales
                      WHERE DATE_FORMAT(sale_date, '%Y-%m') = '$this_month'";

$month_stats = fetch_one($month_stats_query);

// Low stock products
$low_stock_query = "SELECT COUNT(*) as count FROM products WHERE stock_quantity < 20 AND stock_quantity > 0";
$low_stock = fetch_one($low_stock_query);

// Out of stock products
$out_stock_query = "SELECT COUNT(*) as count FROM products WHERE stock_quantity = 0";
$out_stock = fetch_one($out_stock_query);

// Total active sellers
$sellers_query = "SELECT COUNT(*) as count FROM sellers WHERE is_active = 1";
$sellers_count = fetch_one($sellers_query);

// Recent activity
$recent_sales_query = "SELECT 
                        s.sale_date,
                        s.total_revenue,
                        s.total_profit,
                        sel.shop_name,
                        r.route_name
                       FROM sales s
                       JOIN sellers sel ON s.seller_id = sel.seller_id
                       JOIN routes r ON s.route_id = r.route_id
                       ORDER BY s.created_at DESC
                       LIMIT 5";

$recent_sales = fetch_all($recent_sales_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Rathnayake Global ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2C3E50;
            --secondary: #E67E22;
            --accent: #27AE60;
            --danger: #E74C3C;
            --info: #3498DB;
        }
        
        body {
            font-family: 'Work Sans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
            margin: 0;
        }
        
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .hero-header {
            background: white;
            padding: 50px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--secondary), var(--accent), var(--info));
        }
        
        .hero-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--primary);
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .hero-header .company-subtitle {
            font-size: 1.3rem;
            color: #7f8c8d;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .hero-header .tagline {
            font-size: 1rem;
            color: var(--secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .action-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            color: var(--primary);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary), var(--accent));
            transform: scaleX(0);
            transition: transform 0.3s;
        }
        
        .action-card:hover::before {
            transform: scaleX(1);
        }
        
        .action-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            color: var(--primary);
        }
        
        .action-card .icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .action-card .title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .action-card .description {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-box {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
        }
        
        .stat-box .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-box .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin: 5px 0;
        }
        
        .stat-box .stat-label {
            font-size: 0.85rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            background: white;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary), #34495e);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px 25px;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .section-title {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 40px 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .profit-positive {
            color: var(--accent);
            font-weight: 700;
        }
        
        .alert-box {
            background: white;
            border-left: 5px solid var(--danger);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .alert-box .alert-icon {
            font-size: 2rem;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Hero Header -->
        <div class="hero-header">
            <div class="tagline">Welcome to</div>
            <h1>Rathnayake Global</h1>
            <div class="company-subtitle">Enterprise Resource Planning System</div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="daily_sales.php" class="action-card">
                <span class="icon">📝</span>
                <div class="title">Daily Sales</div>
                <div class="description">Record new transactions</div>
            </a>
            
            <a href="report.php" class="action-card">
                <span class="icon">📊</span>
                <div class="title">Reports</div>
                <div class="description">View analytics & insights</div>
            </a>
            
            <a href="inventory.php" class="action-card">
                <span class="icon">📦</span>
                <div class="title">Inventory</div>
                <div class="description">Manage products & stock</div>
            </a>
        </div>
        
        <!-- Today's Performance -->
        <h2 class="section-title">📅 Today's Performance</h2>
        
        <div class="stat-grid">
            <div class="stat-box text-center">
                <div class="stat-icon">🛒</div>
                <div class="stat-label">Sales Today</div>
                <div class="stat-value"><?php echo $today_stats['total_sales'] ?? 0; ?></div>
            </div>
            
            <div class="stat-box text-center">
                <div class="stat-icon">💰</div>
                <div class="stat-label">Revenue Today</div>
                <div class="stat-value"><?php echo format_currency($today_stats['total_revenue'] ?? 0); ?></div>
            </div>
            
            <div class="stat-box text-center">
                <div class="stat-icon">📈</div>
                <div class="stat-label">Profit Today</div>
                <div class="stat-value profit-positive"><?php echo format_currency($today_stats['total_profit'] ?? 0); ?></div>
            </div>
        </div>
        
        <!-- This Month's Performance -->
        <h2 class="section-title">📆 This Month's Performance</h2>
        
        <div class="stat-grid">
            <div class="stat-box text-center">
                <div class="stat-icon">🛒</div>
                <div class="stat-label">Total Sales</div>
                <div class="stat-value"><?php echo $month_stats['total_sales'] ?? 0; ?></div>
            </div>
            
            <div class="stat-box text-center">
                <div class="stat-icon">💰</div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value"><?php echo format_currency($month_stats['total_revenue'] ?? 0); ?></div>
            </div>
            
            <div class="stat-box text-center">
                <div class="stat-icon">📈</div>
                <div class="stat-label">Total Profit</div>
                <div class="stat-value profit-positive"><?php echo format_currency($month_stats['total_profit'] ?? 0); ?></div>
            </div>
            
            <div class="stat-box text-center">
                <div class="stat-icon">🏪</div>
                <div class="stat-label">Active Shops</div>
                <div class="stat-value"><?php echo $sellers_count['count'] ?? 0; ?></div>
            </div>
        </div>
        
        <!-- Alerts -->
        <?php if (($low_stock['count'] ?? 0) > 0 || ($out_stock['count'] ?? 0) > 0): ?>
            <h2 class="section-title">⚠️ Inventory Alerts</h2>
            
            <?php if (($out_stock['count'] ?? 0) > 0): ?>
                <div class="alert-box">
                    <div class="d-flex align-items-center">
                        <span class="alert-icon">🚨</span>
                        <div>
                            <strong>Out of Stock Alert!</strong>
                            <p class="mb-0"><?php echo $out_stock['count']; ?> product(s) are currently out of stock. Please reorder immediately.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (($low_stock['count'] ?? 0) > 0): ?>
                <div class="alert-box" style="border-left-color: var(--secondary);">
                    <div class="d-flex align-items-center">
                        <span class="alert-icon">⚠️</span>
                        <div>
                            <strong>Low Stock Warning</strong>
                            <p class="mb-0"><?php echo $low_stock['count']; ?> product(s) are running low on stock. Consider reordering soon.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Recent Activity -->
        <h2 class="section-title">🕒 Recent Activity</h2>
        
        <div class="card">
            <div class="card-header">
                Latest Sales Transactions
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Shop Name</th>
                                <th>Route</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_sales)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No recent sales activity
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_sales as $sale): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($sale['sale_date'])); ?></td>
                                        <td><strong><?php echo $sale['shop_name']; ?></strong></td>
                                        <td><span class="badge bg-secondary"><?php echo $sale['route_name']; ?></span></td>
                                        <td class="text-end"><?php echo format_currency($sale['total_revenue']); ?></td>
                                        <td class="text-end profit-positive"><?php echo format_currency($sale['total_profit']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div style="height: 50px;"></div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
