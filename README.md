# Rathnayake Global Enterprises - ERP System

## Professional Enterprise Resource Planning System for Bites and Sweets Distribution

A complete web-based management system built with PHP and MySQL for managing inventory, sales, routes, and generating comprehensive business analytics.

---

## 📋 System Overview

This ERP system is designed for a "Bites and Sweets" distribution company with the following business model:

**Purchase from Suppliers → Store Inventory → Distribute to Retail Shops via Specific Routes**

### Key Features

✅ **Inventory Management**
- Manage suppliers (Name, Contact, Address)
- Track products with buying price, selling price, and stock quantity
- Real-time stock level monitoring
- Low stock alerts

✅ **Route & Seller Management**
- Multiple distribution routes (Colombo, Gampaha, Kandy, Negombo, etc.)
- Seller/shop database with owner information
- Route-based shop filtering

✅ **Sales & Distribution Engine**
- Daily sales entry with route and shop selection
- Automatic calculations:
  - Total Revenue = Selling Price × Quantity
  - Total Profit = (Selling Price - Buying Price) × Quantity
- Real-time stock updates
- Transaction history tracking

✅ **Reporting & Analytics**
- Daily dashboard with sales and profit totals
- Monthly summary reports
- Route performance analytics
- Top products by profit
- Recent transaction history
- Profit margin calculations

✅ **Mobile-Responsive Design**
- Bootstrap-based responsive UI
- Works on desktop, tablet, and mobile devices
- Perfect for sales representatives in the field

---

## 🚀 Installation Instructions

### Prerequisites

- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **Web Server**: Apache or Nginx
- **XAMPP/WAMP/MAMP** (recommended for local development)

### Step 1: Database Setup

1. Start your MySQL server (via XAMPP/WAMP control panel)

2. Open phpMyAdmin or MySQL command line

3. Import the database schema:
   ```sql
   mysql -u root -p < database_schema.sql
   ```
   
   Or manually:
   - Open phpMyAdmin
   - Click "New" to create a database
   - Click "Import" tab
   - Select `database_schema.sql` file
   - Click "Go"

4. The database `rathnayake_erp` will be created with:
   - All required tables
   - Sample data (suppliers, products, routes, sellers)
   - Useful views for reporting
   - Admin user (username: `admin`, password: `admin123`)

### Step 2: Configure Database Connection

1. Open `db_connection.php`

2. Update the database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Your MySQL password
   define('DB_NAME', 'rathnayake_erp');
   ```

3. Save the file

### Step 3: Deploy Files

1. Copy all PHP files to your web server directory:
   - **XAMPP**: `C:/xampp/htdocs/rathnayake_erp/`
   - **WAMP**: `C:/wamp64/www/rathnayake_erp/`
   - **MAMP**: `/Applications/MAMP/htdocs/rathnayake_erp/`

2. Ensure all files have proper read permissions

### Step 4: Access the System

1. Start your web server (Apache)

2. Open your web browser and navigate to:
   ```
   http://localhost/rathnayake_erp/
   ```

3. You should see the dashboard!

---

## 📁 File Structure

```
rathnayake_erp/
│
├── database_schema.sql      # Complete database schema with sample data
├── db_connection.php        # Database connection and helper functions
├── index.php               # Homepage/Dashboard
├── daily_sales.php         # Daily sales entry form
├── get_sellers.php         # AJAX endpoint for loading sellers by route
├── report.php              # Reports & analytics page
├── inventory.php           # Inventory management page
└── README.md              # This file
```

---

## 🎯 Usage Guide

### 1. Dashboard (index.php)

The main dashboard shows:
- Today's sales, revenue, and profit
- This month's performance metrics
- Inventory alerts (low stock warnings)
- Recent activity

**Navigation**: Click any quick action card to access different modules.

### 2. Daily Sales Entry (daily_sales.php)

To record a new sale:

1. Select the **Sale Date** (defaults to today)
2. Choose a **Route** from the dropdown
3. Select a **Shop** (filtered by the selected route)
4. Click **"+ Add Product"** to add items
5. For each product:
   - Select the product from dropdown (shows current stock)
   - Enter the quantity sold
6. Add multiple products as needed
7. Click **"💾 Save Sale"**

**Automatic Calculations**:
- System calculates total revenue and profit automatically
- Stock levels are updated in real-time
- Low stock warnings are displayed

### 3. Reports & Analytics (report.php)

View comprehensive business insights:

**Daily Reports**:
- Filter by date to see specific day's performance
- Total revenue, profit, transactions
- Active shops for the day

**Monthly Reports**:
- 12-month history
- Revenue and profit trends
- Average profit per sale

**Route Analytics**:
- Ranked by profit (Gold 🥇, Silver 🥈, Bronze 🥉)
- Shows which route is most profitable
- Active sellers per route

**Additional Insights**:
- Top 10 products by profit
- Recent transactions

### 4. Inventory Management (inventory.php)

Monitor your product catalog:

- **Search**: Real-time search by product or supplier name
- **Stock Status**: Color-coded indicators (Good/Medium/Low/Out of Stock)
- **Profit Margins**: See profit per unit and percentage
- **Inventory Value**: Total value of stock on hand
- **Suppliers Directory**: Contact information for all suppliers

---

## 📊 Database Schema Overview

### Main Tables

1. **suppliers**: Supplier information
2. **products**: Product catalog with pricing and stock
3. **routes**: Distribution routes
4. **sellers**: Retail shops/sellers
5. **sales**: Sales transaction headers
6. **sales_items**: Individual items in each sale
7. **users**: System users (optional authentication)

### Relationships

```
suppliers (1) → (N) products
routes (1) → (N) sellers
routes (1) → (N) sales
sellers (1) → (N) sales
sales (1) → (N) sales_items
products (1) → (N) sales_items
```

### Pre-built Views

- `daily_sales_summary`: Daily performance metrics
- `monthly_sales_summary`: Monthly aggregates
- `route_performance`: Route-wise analytics
- `product_performance`: Product sales statistics
- `seller_performance`: Seller purchase history

---

## 🎨 Design Features

### Modern UI/UX

- **Color Scheme**: Professional gradient backgrounds (purple to violet)
- **Typography**: Playfair Display (headers) + Work Sans (body)
- **Interactive Elements**: Hover effects, smooth transitions
- **Responsive Design**: Works on all screen sizes
- **Visual Feedback**: Success/error messages, loading states

### Accessibility

- High contrast text
- Clear visual hierarchy
- Large touch targets for mobile
- Keyboard navigation support

---

## 🔧 Customization

### Adding New Routes

```sql
INSERT INTO routes (route_name, route_description) 
VALUES ('New Route Name', 'Description of the route');
```

### Adding New Products

```sql
INSERT INTO products (product_name, buying_price, selling_price, stock_quantity, supplier_id) 
VALUES ('Product Name', 100.00, 150.00, 200, 1);
```

### Adding New Sellers

```sql
INSERT INTO sellers (shop_name, owner_name, contact_number, address, route_id) 
VALUES ('Shop Name', 'Owner Name', '0771234567', 'Shop Address', 1);
```

---

## 📈 Reporting Capabilities

The system provides automatic calculation of:

1. **Revenue**: Total sales amount
2. **Profit**: Net profit after cost of goods
3. **Profit Margin**: Percentage profit
4. **Average Profit per Sale**: Mean profit across transactions
5. **Route Comparison**: Which routes perform best
6. **Product Performance**: Best-selling items
7. **Seller Activity**: Purchase frequency and volume

---

## 🔒 Security Features

- SQL injection prevention via parameterized queries
- XSS protection via input sanitization
- CSRF token support (can be enhanced)
- Password hashing for user accounts (bcrypt)
- Session management
- Database transaction support for data integrity

---

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**:
- Check MySQL is running
- Verify credentials in `db_connection.php`
- Ensure database exists

**No Products Showing**:
- Check if products table has data
- Run the sample data inserts from `database_schema.sql`

**Sellers Not Loading**:
- Verify `get_sellers.php` is accessible
- Check browser console for JavaScript errors
- Ensure route has assigned sellers

**Sales Not Saving**:
- Check stock availability
- Verify all required fields are filled
- Check browser console and PHP error logs

---

## 📞 Support

For issues or questions:
- Check the database schema comments
- Review PHP error logs
- Inspect browser console for JavaScript errors
- Verify all file paths are correct

---

## 🎓 Learning Resources

This system demonstrates:
- PHP procedural and OOP concepts
- MySQL database design and relationships
- AJAX for dynamic content loading
- Bootstrap responsive design
- Business logic implementation
- Transaction management
- Data analytics and reporting

---

## 📝 License

This project is developed for Rathnayake Global Enterprises (Pvt) Ltd.

---

## 🙏 Acknowledgments

Built with:
- PHP 8.x
- MySQL 8.x
- Bootstrap 5.3
- Google Fonts (Playfair Display, Work Sans)

---

## 🚀 Future Enhancements

Potential improvements:
- User authentication and role-based access
- Export reports to PDF/Excel
- Email notifications for low stock
- Advanced charts and visualizations
- Mobile app version
- Barcode scanning integration
- Multi-currency support
- Delivery tracking
- Customer loyalty program

---

**Developed by**: Senior PHP Development Team  
**Version**: 1.0.0  
**Last Updated**: January 2026
"# rg_inventory" 
