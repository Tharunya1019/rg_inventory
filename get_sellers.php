<?php
/**
 * Get Sellers by Route (AJAX Endpoint)
 * Returns JSON list of sellers for a specific route
 */

require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['route_id']) || empty($_GET['route_id'])) {
    echo json_encode([]);
    exit;
}

$route_id = intval($_GET['route_id']);

$query = "SELECT seller_id, shop_name, owner_name, contact_number 
          FROM sellers 
          WHERE route_id = $route_id AND is_active = 1 
          ORDER BY shop_name";

$sellers = fetch_all($query);

echo json_encode($sellers);
?>
