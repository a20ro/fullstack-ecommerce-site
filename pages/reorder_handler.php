<?php
require_once __DIR__ . '/../config/database.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = (int)($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

try {
    // Verify order belongs to user
    $order_sql = "SELECT id FROM orders WHERE id = ? AND user_id = ?";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param('ii', $order_id, $user_id);
    $order_stmt->execute();
    $order = $order_stmt->get_result()->fetch_assoc();
    
    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }
    
    // Get order items
    $items_sql = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param('i', $order_id);
    $items_stmt->execute();
    $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (empty($items)) {
        echo json_encode(['success' => false, 'error' => 'No items found in order']);
        exit;
    }
    
    $added_count = 0;
    $errors = [];
    
    // Add each item to cart
    foreach ($items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        
        // Check if product still exists
        $product_sql = "SELECT id, name FROM products WHERE id = ?";
        $product_stmt = $conn->prepare($product_sql);
        $product_stmt->bind_param('i', $product_id);
        $product_stmt->execute();
        $product = $product_stmt->get_result()->fetch_assoc();
        
        if (!$product) {
            $errors[] = "Product ID {$product_id} no longer available";
            continue;
        }
        
        // Check if item already in cart
        $existing_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
        $existing_stmt = $conn->prepare($existing_sql);
        $existing_stmt->bind_param('ii', $user_id, $product_id);
        $existing_stmt->execute();
        $existing = $existing_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            // Update existing cart item
            $new_quantity = $existing['quantity'] + $quantity;
            $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('ii', $new_quantity, $existing['id']);
            $update_stmt->execute();
        } else {
            // Add new cart item
            $insert_sql = "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param('iii', $user_id, $product_id, $quantity);
            $insert_stmt->execute();
        }
        
        $added_count++;
    }
    
    if ($added_count > 0) {
        $message = "Successfully added {$added_count} item(s) to your cart";
        if (!empty($errors)) {
            $message .= ". Note: " . implode(', ', $errors);
        }
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No items could be added to cart. ' . implode(', ', $errors)]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
