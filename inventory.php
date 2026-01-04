<?php
/**
 * Inventory Management Page
 * Rathnayake Global Enterprises ERP System
 * 
 * Manage products, suppliers, and stock levels
 */

require_once 'db_connection.php';

// Fetch all products with supplier information
$products_query = "SELECT 
                    p.product_id,
                    p.product_name,
                    p.buying_price,
                    p.selling_price,
                    p.stock_quantity,
                    s.supplier_name,
                    (p.selling_price - p.buying_price) as profit_margin,
                    ((p.selling_price - p.buying_price) / p.buying_price * 100) as profit_percentage
                   FROM products p
                   LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                   ORDER BY p.product_name";

$products = fetch_all($products_query);

// Fetch suppliers
$suppliers_query = "SELECT * FROM suppliers ORDER BY supplier_name";
$suppliers = fetch_all($suppliers_query);

// Calculate inventory statistics
$total_products = count($products);
$low_stock_count = 0;
$total_inventory_value = 0;

foreach ($products as $product) {
    if ($product['stock_quantity'] < 20) {
        $low_stock_count++;
    }
    $total_inventory_value += $product['buying_price'] * $product['stock_quantity'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Rathnayake Global ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2C3E50;
            --secondary: #E67E22;
            --accent: #27AE60;
            --danger: #E74C3C;
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
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
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
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--primary);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .stock-badge {
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .stock-critical {
            background: #fee;
            color: #c00;
        }
        
        .stock-low {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-good {
            background: #d4edda;
            color: #155724;
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
        
        .search-box {
            background: white;
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .profit-positive {
            color: var(--accent);
            font-weight: 600;
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
            <h1>Inventory Management</h1>
            <p class="subtitle" style="color: #7f8c8d; margin: 5px 0 0 0;">Manage products, suppliers, and stock levels</p>
        </div>
        
        <!-- Inventory Statistics -->
        <div class="row">
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="icon">📦</div>
                    <div class="label">Total Products</div>
                    <div class="value"><?php echo $total_products; ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="icon">⚠️</div>
                    <div class="label">Low Stock Items</div>
                    <div class="value" style="color: var(--danger);"><?php echo $low_stock_count; ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="icon">💵</div>
                    <div class="label">Inventory Value</div>
                    <div class="value"><?php echo format_currency($total_inventory_value); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Search Box -->
        <div class="search-box">
            <input type="text" class="form-control" id="searchInput" placeholder="🔍 Search products by name...">
        </div>
        
        <!-- Products Table -->
        <div class="card">
            <div class="card-header">
                Product Catalog
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="productsTable">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Supplier</th>
                                <th class="text-end">Buying Price</th>
                                <th class="text-end">Selling Price</th>
                                <th class="text-end">Profit Margin</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): 
                                $stock = $product['stock_quantity'];
                                $stock_status = 'Good';
                                $stock_class = 'stock-good';
                                
                                if ($stock == 0) {
                                    $stock_status = 'Out of Stock';
                                    $stock_class = 'stock-critical';
                                } elseif ($stock < 20) {
                                    $stock_status = 'Low Stock';
                                    $stock_class = 'stock-low';
                                } elseif ($stock < 50) {
                                    $stock_status = 'Medium';
                                    $stock_class = 'stock-low';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $product['product_name']; ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo $product['supplier_name'] ?? 'N/A'; ?></small>
                                    </td>
                                    <td class="text-end">
                                        <?php echo format_currency($product['buying_price']); ?>
                                    </td>
                                    <td class="text-end">
                                        <?php echo format_currency($product['selling_price']); ?>
                                    </td>
                                    <td class="text-end profit-positive">
                                        <?php echo format_currency($product['profit_margin']); ?>
                                        <small class="text-muted d-block">
                                            (<?php echo number_format($product['profit_percentage'], 1); ?>%)
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <strong style="font-size: 1.1rem;"><?php echo $stock; ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="stock-badge <?php echo $stock_class; ?>">
                                            <?php echo $stock_status; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Suppliers List -->
        <div class="card">
            <div class="card-header">
                Suppliers Directory
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Supplier Name</th>
                                <th>Contact Number</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td><strong><?php echo $supplier['supplier_name']; ?></strong></td>
                                    <td><?php echo $supplier['contact_number']; ?></td>
                                    <td><?php echo $supplier['address']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div style="height: 50px;"></div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#productsTable tbody tr');
            
            tableRows.forEach(row => {
                const productName = row.cells[0].textContent.toLowerCase();
                const supplierName = row.cells[1].textContent.toLowerCase();
                
                if (productName.includes(searchText) || supplierName.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
