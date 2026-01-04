<?php
/**
 * Reports & Analytics Page
 * Rathnayake Global Enterprises ERP System
 * 
 * Displays daily, monthly, and route-wise profit analytics
 */

require_once 'db_connection.php';

// Get today's date
$today = date('Y-m-d');

// Get filter parameters
$filter_date = isset($_GET['filter_date']) ? sanitize_input($_GET['filter_date']) : $today;
$filter_month = isset($_GET['filter_month']) ? sanitize_input($_GET['filter_month']) : date('Y-m');

// Fetch Daily Sales Summary
$daily_query = "SELECT 
                    s.sale_date,
                    COUNT(DISTINCT s.sale_id) as total_transactions,
                    COUNT(DISTINCT s.seller_id) as unique_sellers,
                    SUM(s.total_revenue) as total_revenue,
                    SUM(s.total_profit) as total_profit,
                    AVG(s.total_profit) as avg_profit_per_sale
                FROM sales s
                WHERE s.sale_date = '$filter_date'
                GROUP BY s.sale_date";

$daily_stats = fetch_one($daily_query);

// If no data for selected date, set zeros
if (!$daily_stats) {
    $daily_stats = [
        'total_transactions' => 0,
        'unique_sellers' => 0,
        'total_revenue' => 0,
        'total_profit' => 0,
        'avg_profit_per_sale' => 0
    ];
}

// Fetch Monthly Sales Summary
$monthly_query = "SELECT 
                    DATE_FORMAT(s.sale_date, '%Y-%m') as month,
                    COUNT(DISTINCT s.sale_id) as total_transactions,
                    COUNT(DISTINCT s.seller_id) as unique_sellers,
                    SUM(s.total_revenue) as total_revenue,
                    SUM(s.total_profit) as total_profit,
                    AVG(s.total_profit) as avg_profit_per_sale
                  FROM sales s
                  GROUP BY DATE_FORMAT(s.sale_date, '%Y-%m')
                  ORDER BY month DESC
                  LIMIT 12";

$monthly_stats = fetch_all($monthly_query);

// Fetch Route Performance
$route_query = "SELECT 
                    r.route_id,
                    r.route_name,
                    COUNT(DISTINCT s.sale_id) as total_sales,
                    COUNT(DISTINCT s.seller_id) as active_sellers,
                    SUM(s.total_revenue) as total_revenue,
                    SUM(s.total_profit) as total_profit,
                    AVG(s.total_profit) as avg_profit_per_sale,
                    MAX(s.sale_date) as last_sale_date
                FROM routes r
                LEFT JOIN sales s ON r.route_id = s.route_id
                WHERE r.is_active = 1
                GROUP BY r.route_id, r.route_name
                ORDER BY total_profit DESC";

$route_stats = fetch_all($route_query);

// Fetch Top Products
$top_products_query = "SELECT 
                        p.product_name,
                        SUM(si.quantity) as total_quantity_sold,
                        SUM(si.revenue) as total_revenue,
                        SUM(si.profit) as total_profit
                       FROM sales_items si
                       JOIN products p ON si.product_id = p.product_id
                       GROUP BY p.product_id, p.product_name
                       ORDER BY total_profit DESC
                       LIMIT 10";

$top_products = fetch_all($top_products_query);

// Fetch Recent Transactions
$recent_query = "SELECT 
                    s.sale_id,
                    s.sale_date,
                    sel.shop_name,
                    r.route_name,
                    s.total_revenue,
                    s.total_profit,
                    COUNT(si.sale_item_id) as item_count
                 FROM sales s
                 JOIN sellers sel ON s.seller_id = sel.seller_id
                 JOIN routes r ON s.route_id = r.route_id
                 LEFT JOIN sales_items si ON s.sale_id = si.sale_id
                 GROUP BY s.sale_id
                 ORDER BY s.created_at DESC
                 LIMIT 10";

$recent_transactions = fetch_all($recent_query);

// Calculate profit margin percentage
function calculate_margin($revenue, $profit) {
    return $revenue > 0 ? ($profit / $revenue) * 100 : 0;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Rathnayake Global ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2C3E50;
            --secondary: #E67E22;
            --accent: #27AE60;
            --danger: #E74C3C;
            --info: #3498DB;
            --warning: #F39C12;
        }
        
        body {
            font-family: 'Work Sans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            margin: 0;
            font-size: 2.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin: 10px 0;
        }
        
        .stat-card .label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary), #34495e);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px 25px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .table {
            margin: 0;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--primary);
            border-bottom: 2px solid var(--secondary);
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
        }
        
        .navigation {
            background: white;
            padding: 15px 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .navigation a {
            color: var(--primary);
            text-decoration: none;
            margin-right: 20px;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .navigation a:hover {
            color: var(--secondary);
        }
        
        .filter-section {
            background: white;
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .filter-section .form-label {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .profit-positive {
            color: var(--accent);
            font-weight: 600;
        }
        
        .profit-negative {
            color: var(--danger);
            font-weight: 600;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Navigation -->
        <div class="navigation">
            <a href="index.php">🏠 Home</a>
            <a href="daily_sales.php">📝 Daily Sales</a>
            <a href="report.php">📊 Reports</a>
            <a href="inventory.php">📦 Inventory</a>
        </div>
        
        <!-- Page Header -->
        <div class="page-header">
            <h1>Reports & Analytics</h1>
            <p class="subtitle" style="color: #7f8c8d; margin: 5px 0 0 0;">Comprehensive business insights and performance metrics</p>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="" class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Daily Report Date</label>
                    <input type="date" class="form-control" name="filter_date" value="<?php echo $filter_date; ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">📅 Update Report</button>
                </div>
            </form>
        </div>
        
        <!-- Daily Statistics -->
        <h3 style="color: white; margin: 30px 0 20px 0; font-weight: 700;">📅 Daily Performance - <?php echo date('F j, Y', strtotime($filter_date)); ?></h3>
        
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="icon">💰</div>
                    <div class="label">Total Revenue</div>
                    <div class="value"><?php echo format_currency($daily_stats['total_revenue']); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="icon">📈</div>
                    <div class="label">Net Profit</div>
                    <div class="value profit-positive"><?php echo format_currency($daily_stats['total_profit']); ?></div>
                    <small style="color: #7f8c8d;">
                        Margin: <?php echo number_format(calculate_margin($daily_stats['total_revenue'], $daily_stats['total_profit']), 1); ?>%
                    </small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="icon">🛒</div>
                    <div class="label">Transactions</div>
                    <div class="value"><?php echo $daily_stats['total_transactions']; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="icon">🏪</div>
                    <div class="label">Active Shops</div>
                    <div class="value"><?php echo $daily_stats['unique_sellers']; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Monthly Summary -->
        <h3 style="color: white; margin: 40px 0 20px 0; font-weight: 700;">📊 Monthly Performance Summary</h3>
        
        <div class="card">
            <div class="card-header">
                Monthly Revenue & Profit Breakdown
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Transactions</th>
                                <th class="text-center">Active Shops</th>
                                <th class="text-end">Total Revenue</th>
                                <th class="text-end">Total Profit</th>
                                <th class="text-center">Margin %</th>
                                <th class="text-end">Avg Profit/Sale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($monthly_stats)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No sales data available
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($monthly_stats as $month): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?php echo $month['total_transactions']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $month['unique_sellers']; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php echo format_currency($month['total_revenue']); ?>
                                        </td>
                                        <td class="text-end profit-positive">
                                            <?php echo format_currency($month['total_profit']); ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                <?php echo number_format(calculate_margin($month['total_revenue'], $month['total_profit']), 1); ?>%
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <?php echo format_currency($month['avg_profit_per_sale']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Route Performance -->
        <h3 style="color: white; margin: 40px 0 20px 0; font-weight: 700;">🛣️ Route Performance Analytics</h3>
        
        <div class="card">
            <div class="card-header">
                Which Route is Generating the Most Profit?
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Route Name</th>
                                <th class="text-center">Total Sales</th>
                                <th class="text-center">Active Sellers</th>
                                <th class="text-end">Total Revenue</th>
                                <th class="text-end">Total Profit</th>
                                <th class="text-center">Last Sale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($route_stats)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No route data available
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $rank = 1;
                                foreach ($route_stats as $route): 
                                    $medal = '';
                                    if ($rank === 1) $medal = '🥇';
                                    elseif ($rank === 2) $medal = '🥈';
                                    elseif ($rank === 3) $medal = '🥉';
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $medal . ' ' . $rank; ?></strong>
                                        </td>
                                        <td>
                                            <strong><?php echo $route['route_name']; ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?php echo $route['total_sales'] ?? 0; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $route['active_sellers'] ?? 0; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php echo format_currency($route['total_revenue'] ?? 0); ?>
                                        </td>
                                        <td class="text-end profit-positive">
                                            <strong><?php echo format_currency($route['total_profit'] ?? 0); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $route['last_sale_date'] ? date('M j, Y', strtotime($route['last_sale_date'])) : 'N/A'; ?>
                                        </td>
                                    </tr>
                                <?php 
                                    $rank++;
                                endforeach; 
                                ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Two Column Layout -->
        <div class="row">
            <!-- Top Products -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        🏆 Top 10 Products by Profit
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Qty Sold</th>
                                        <th class="text-end">Profit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($top_products)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">
                                                No product data available
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($top_products as $product): ?>
                                            <tr>
                                                <td><?php echo $product['product_name']; ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary"><?php echo $product['total_quantity_sold']; ?></span>
                                                </td>
                                                <td class="text-end profit-positive">
                                                    <?php echo format_currency($product['total_profit']); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        🕒 Recent Transactions
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Shop</th>
                                        <th>Route</th>
                                        <th class="text-end">Profit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_transactions)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                No recent transactions
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_transactions as $txn): ?>
                                            <tr>
                                                <td><?php echo date('M j', strtotime($txn['sale_date'])); ?></td>
                                                <td><?php echo $txn['shop_name']; ?></td>
                                                <td>
                                                    <small class="text-muted"><?php echo $txn['route_name']; ?></small>
                                                </td>
                                                <td class="text-end profit-positive">
                                                    <?php echo format_currency($txn['total_profit']); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="height: 50px;"></div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
