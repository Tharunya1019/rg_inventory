<?php
/**
 * Database Connection File
 * Rathnayake Global Enterprises ERP System
 * 
 * This file establishes a connection to the MySQL database
 * and provides helper functions for database operations
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rathnayake_erp');

// Timezone Configuration
date_default_timezone_set('Asia/Colombo');

// Create Connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check Connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set Character Set to UTF-8
mysqli_set_charset($conn, "utf8mb4");

/**
 * Escape string for safe SQL queries
 * @param string $value - The value to escape
 * @return string - Escaped value
 */
function escape_string($value) {
    global $conn;
    return mysqli_real_escape_string($conn, $value);
}

/**
 * Execute a query and return the result
 * @param string $query - SQL query to execute
 * @return mixed - Query result or false on failure
 */
function execute_query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("Query Error: " . mysqli_error($conn));
        return false;
    }
    
    return $result;
}

/**
 * Fetch all rows from a query result
 * @param string $query - SQL query to execute
 * @return array - Array of associative arrays
 */
function fetch_all($query) {
    $result = execute_query($query);
    
    if (!$result) {
        return [];
    }
    
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    
    return $rows;
}

/**
 * Fetch a single row from a query result
 * @param string $query - SQL query to execute
 * @return array|null - Associative array or null
 */
function fetch_one($query) {
    $result = execute_query($query);
    
    if (!$result) {
        return null;
    }
    
    return mysqli_fetch_assoc($result);
}

/**
 * Get the last inserted ID
 * @return int - Last insert ID
 */
function get_last_insert_id() {
    global $conn;
    return mysqli_insert_id($conn);
}

/**
 * Format currency for display
 * @param float $amount - Amount to format
 * @return string - Formatted currency string
 */
function format_currency($amount) {
    return 'Rs. ' . number_format($amount, 2);
}

/**
 * Format date for display
 * @param string $date - Date string
 * @param string $format - Desired format
 * @return string - Formatted date
 */
function format_date($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

/**
 * Check if a value is set and not empty
 * @param mixed $value - Value to check
 * @return bool - True if set and not empty
 */
function is_set($value) {
    return isset($value) && !empty($value);
}

/**
 * Sanitize input data
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Begin transaction
 */
function begin_transaction() {
    global $conn;
    mysqli_begin_transaction($conn);
}

/**
 * Commit transaction
 */
function commit_transaction() {
    global $conn;
    mysqli_commit($conn);
}

/**
 * Rollback transaction
 */
function rollback_transaction() {
    global $conn;
    mysqli_rollback($conn);
}

/**
 * Close database connection
 */
function close_connection() {
    global $conn;
    mysqli_close($conn);
}

// Session Management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set flash message
 * @param string $type - Message type (success, error, warning, info)
 * @param string $message - Message text
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null - Flash message array or null
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Check if user is logged in
 * @return bool - True if logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null - User ID or null
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * @return string|null - Username or null
 */
function get_current_username() {
    return $_SESSION['username'] ?? null;
}

?>
