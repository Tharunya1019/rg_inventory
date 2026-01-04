<?php
/**
 * Daily Sales Entry Page
 * Rathnayake Global Enterprises ERP System
 * 
 * This page allows sales representatives to enter daily sales data
 * with automatic revenue and profit calculations
 */

require_once 'db_connection.php';

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_sale'])) {
    // Get form data
    $route_id = intval($_POST['route_id']);
    $seller_id = intval($_POST['seller_id']);
    $sale_date = sanitize_input($_POST['sale_date']);
    $notes = sanitize_input($_POST['notes']);
    
    // Get products and quantities
    $products = $_POST['products'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    
    // Validate input
    if (empty($route_id) || empty($seller_id) || empty($sale_date)) {
        $error_message = "Please fill in all required fields.";
    } elseif (empty($products) || empty($quantities)) {
        $error_message = "Please add at least one product to the sale.";
    } else {
        // Begin transaction
        begin_transaction();
        
        try {
            $total_revenue = 0;
            $total_profit = 0;
            $valid_items = [];
            
            // Calculate totals and validate stock
            foreach ($products as $index => $product_id) {
                $product_id = intval($product_id);
                $quantity = intval($quantities[$index]);
                
                if ($quantity <= 0) continue;
                
                // Get product details
                $product_query = "SELECT product_id, buying_price, selling_price, stock_quantity 
                                 FROM products WHERE product_id = $product_id";
                $product = fetch_one($product_query);
                
                if (!$product) {
                    throw new Exception("Invalid product selected.");
                }
                
                // Check stock availability
                if ($product['stock_quantity'] < $quantity) {
                    throw new Exception("Insufficient stock for {$product['product_id']}. Available: {$product['stock_quantity']}");
                }
                
                // Calculate revenue and profit for this item
                $revenue = $product['selling_price'] * $quantity;
                $profit = ($product['selling_price'] - $product['buying_price']) * $quantity;
                
                $total_revenue += $revenue;
                $total_profit += $profit;
                
                $valid_items[] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'buying_price' => $product['buying_price'],
                    'selling_price' => $product['selling_price'],
                    'revenue' => $revenue,
                    'profit' => $profit
                ];
            }
            
            if (empty($valid_items)) {
                throw new Exception("No valid items in the sale.");
            }
            
            // Insert main sales record
            $insert_sale_query = "INSERT INTO sales (seller_id, route_id, sale_date, total_revenue, total_profit, notes, created_by) 
                                 VALUES ($seller_id, $route_id, '$sale_date', $total_revenue, $total_profit, '$notes', '" . get_current_username() . "')";
            
            if (!execute_query($insert_sale_query)) {
                throw new Exception("Failed to create sale record.");
            }
            
            $sale_id = get_last_insert_id();
            
            // Insert sale items and update stock
            foreach ($valid_items as $item) {
                // Insert sale item
                $insert_item_query = "INSERT INTO sales_items (sale_id, product_id, quantity, buying_price, selling_price, revenue, profit)
                                     VALUES ($sale_id, {$item['product_id']}, {$item['quantity']}, {$item['buying_price']}, 
                                             {$item['selling_price']}, {$item['revenue']}, {$item['profit']})";
                
                if (!execute_query($insert_item_query)) {
                    throw new Exception("Failed to add sale item.");
                }
                
                // Update product stock
                $update_stock_query = "UPDATE products SET stock_quantity = stock_quantity - {$item['quantity']} 
                                      WHERE product_id = {$item['product_id']}";
                
                if (!execute_query($update_stock_query)) {
                    throw new Exception("Failed to update stock.");
                }
            }
            
            // Commit transaction
            commit_transaction();
            
            $success_message = "Sale recorded successfully! Total Revenue: " . format_currency($total_revenue) . 
                             " | Total Profit: " . format_currency($total_profit);
            
            // Clear form
            $_POST = [];
            
        } catch (Exception $e) {
            // Rollback on error
            rollback_transaction();
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch routes for dropdown
$routes_query = "SELECT route_id, route_name FROM routes WHERE is_active = 1 ORDER BY route_name";
$routes = fetch_all($routes_query);

// Fetch products for selection
$products_query = "SELECT product_id, product_name, selling_price, stock_quantity 
                   FROM products WHERE stock_quantity > 0 ORDER BY product_name";
$products_list = fetch_all($products_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Entry - Rathnayake Global ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2C3E50;
            --secondary: #E67E22;
            --accent: #27AE60;
            --danger: #E74C3C;
            --light-bg: #ECF0F1;
            --dark-text: #2C3E50;
        }
        
        body {
            font-family: 'Work Sans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .main-container {
            max-width: 1200px;
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
        
        .page-header .subtitle {
            color: #7f8c8d;
            margin-top: 5px;
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
        
        .card-body {
            padding: 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(230, 126, 34, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary), #d35400);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(230, 126, 34, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--accent), #229954);
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #c0392b);
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
        }
        
        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
        }
        
        .product-row {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .product-row:hover {
            border-color: var(--secondary);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        #products-container {
            max-height: 500px;
            overflow-y: auto;
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
        
        .stock-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .stock-low {
            background: #fee;
            color: #c00;
        }
        
        .stock-medium {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-good {
            background: #d4edda;
            color: #155724;
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
            <h1>Daily Sales Entry</h1>
            <p class="subtitle">Record sales transactions and manage inventory</p>
        </div>
        
        <!-- Alert Messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <strong>✓ Success!</strong> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>✗ Error!</strong> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Sales Form -->
        <div class="card">
            <div class="card-header">
                New Sales Transaction
            </div>
            <div class="card-body">
                <form method="POST" action="" id="sales-form">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sale_date" class="form-label">Sale Date *</label>
                            <input type="date" class="form-control" id="sale_date" name="sale_date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="route_id" class="form-label">Select Route *</label>
                            <select class="form-select" id="route_id" name="route_id" required>
                                <option value="">-- Choose Route --</option>
                                <?php foreach ($routes as $route): ?>
                                    <option value="<?php echo $route['route_id']; ?>">
                                        <?php echo $route['route_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="seller_id" class="form-label">Select Shop *</label>
                            <select class="form-select" id="seller_id" name="seller_id" required disabled>
                                <option value="">-- Select Route First --</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" 
                                      placeholder="Any special notes or comments..."></textarea>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Products</h5>
                        <button type="button" class="btn btn-success" onclick="addProductRow()">
                            + Add Product
                        </button>
                    </div>
                    
                    <div id="products-container"></div>
                    
                    <div class="text-end mt-4">
                        <button type="submit" name="submit_sale" class="btn btn-primary btn-lg">
                            💾 Save Sale
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Products data from PHP
        const products = <?php echo json_encode($products_list); ?>;
        let productRowCounter = 0;
        
        // Load sellers based on route selection
        document.getElementById('route_id').addEventListener('change', function() {
            const routeId = this.value;
            const sellerSelect = document.getElementById('seller_id');
            
            sellerSelect.innerHTML = '<option value="">-- Loading... --</option>';
            sellerSelect.disabled = true;
            
            if (routeId) {
                fetch(`get_sellers.php?route_id=${routeId}`)
                    .then(response => response.json())
                    .then(data => {
                        sellerSelect.innerHTML = '<option value="">-- Select Shop --</option>';
                        data.forEach(seller => {
                            sellerSelect.innerHTML += `<option value="${seller.seller_id}">${seller.shop_name} (${seller.owner_name})</option>`;
                        });
                        sellerSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        sellerSelect.innerHTML = '<option value="">-- Error Loading Shops --</option>';
                    });
            }
        });
        
        // Add product row
        function addProductRow() {
            productRowCounter++;
            const container = document.getElementById('products-container');
            
            let productOptions = '<option value="">-- Select Product --</option>';
            products.forEach(product => {
                const stockClass = product.stock_quantity < 20 ? 'stock-low' : 
                                 product.stock_quantity < 100 ? 'stock-medium' : 'stock-good';
                productOptions += `<option value="${product.product_id}" data-price="${product.selling_price}" data-stock="${product.stock_quantity}">
                    ${product.product_name} - ${product.selling_price} LKR (Stock: ${product.stock_quantity})
                </option>`;
            });
            
            const row = document.createElement('div');
            row.className = 'product-row';
            row.id = `product-row-${productRowCounter}`;
            row.innerHTML = `
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Product</label>
                        <select class="form-select" name="products[]" required onchange="updateProductInfo(this)">
                            ${productOptions}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantities[]" min="1" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-danger w-100" onclick="removeProductRow(${productRowCounter})">
                            🗑️ Remove
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(row);
        }
        
        // Remove product row
        function removeProductRow(id) {
            const row = document.getElementById(`product-row-${id}`);
            if (row) {
                row.remove();
            }
        }
        
        // Update product info when selected
        function updateProductInfo(select) {
            const selectedOption = select.options[select.selectedIndex];
            const stock = selectedOption.getAttribute('data-stock');
            
            if (stock && parseInt(stock) < 20) {
                alert(`⚠️ Warning: Low stock for this product (${stock} units remaining)`);
            }
        }
        
        // Add initial product row
        addProductRow();
        
        // Form validation
        document.getElementById('sales-form').addEventListener('submit', function(e) {
            const products = document.querySelectorAll('select[name="products[]"]');
            if (products.length === 0) {
                e.preventDefault();
                alert('Please add at least one product to the sale.');
            }
        });
    </script>
</body>
</html>
