<?php
require_once __DIR__ . '/../config/database.php';
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'process_order':
        processOrder($conn, $user_id);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function processOrder($conn, $user_id) {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get cart items
        $cart_sql = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price 
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ?";
        
        $cart_stmt = $conn->prepare($cart_sql);
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        
        if ($cart_result->num_rows === 0) {
            throw new Exception('Cart is empty');
        }
        
        $cart_items = [];
        $subtotal = 0;
        
        while ($row = $cart_result->fetch_assoc()) {
            $cart_items[] = $row;
            $subtotal += $row['price'] * $row['quantity'];
        }
        
        // Calculate totals
        $shipping = 0; // Free shipping
        $tax = $subtotal * 0.08; // 8% tax
        $total = $subtotal + $shipping + $tax;
        
        // Validate form data
        $shipping_name = trim($_POST['shipping_name'] ?? '');
        $shipping_email = trim($_POST['shipping_email'] ?? '');
        $shipping_phone = trim($_POST['shipping_phone'] ?? '');
        $shipping_address = trim($_POST['shipping_address'] ?? '');
        $shipping_city = trim($_POST['shipping_city'] ?? '');
        $shipping_state = trim($_POST['shipping_state'] ?? '');
        $shipping_zip = trim($_POST['shipping_zip'] ?? '');
        
        $card_number = trim($_POST['card_number'] ?? '');
        $expiry_date = trim($_POST['expiry_date'] ?? '');
        $cvv = trim($_POST['cvv'] ?? '');
        $card_name = trim($_POST['card_name'] ?? '');
        
        // Basic validation
        if (empty($shipping_name) || empty($shipping_email) || empty($shipping_phone) || 
            empty($shipping_address) || empty($shipping_city) || empty($shipping_state) || 
            empty($shipping_zip) || empty($card_number) || empty($expiry_date) || 
            empty($cvv) || empty($card_name)) {
            throw new Exception('All required fields must be filled');
        }
        
        // Validate email
        if (!filter_var($shipping_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }
        
        // Create order
        $order_sql = "INSERT INTO orders (user_id, shipping_name, shipping_email, shipping_phone, 
                    shipping_address, shipping_city, shipping_state, shipping_zip, 
                    card_last_four, subtotal, shipping, tax, total, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        $card_last_four = substr(str_replace(' ', '', $card_number), -4);
        
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("issssssssdddd", 
            $user_id, $shipping_name, $shipping_email, $shipping_phone, 
            $shipping_address, $shipping_city, $shipping_state, $shipping_zip, 
            $card_last_four, $subtotal, $shipping, $tax, $total);
        
        if (!$order_stmt->execute()) {
            throw new Exception('Failed to create order: ' . $order_stmt->error);
        }
        
        $order_id = $conn->insert_id;
        
        // Create order items
        $order_item_sql = "INSERT INTO order_items (order_id, product_id, product_name, 
                          quantity, price, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        
        $order_item_stmt = $conn->prepare($order_item_sql);
        
        foreach ($cart_items as $item) {
            $order_item_stmt->bind_param("iisid", 
                $order_id, $item['product_id'], $item['name'], 
                $item['quantity'], $item['price']);
            
            if (!$order_item_stmt->execute()) {
                throw new Exception('Failed to create order item: ' . $order_item_stmt->error);
            }
        }
        
        // Clear cart
        $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
        $clear_cart_stmt = $conn->prepare($clear_cart_sql);
        $clear_cart_stmt->bind_param("i", $user_id);
        
        if (!$clear_cart_stmt->execute()) {
            throw new Exception('Failed to clear cart: ' . $clear_cart_stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Order placed successfully!',
            'order_id' => $order_id,
            'total' => $total
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
