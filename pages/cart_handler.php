<?php
require_once __DIR__ . '/../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in to add items to your cart']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        addToCart($conn, $user_id);
        break;
    case 'update':
        updateCartItem($conn, $user_id);
        break;
    case 'remove':
        removeFromCart($conn, $user_id);
        break;
    case 'get_count':
        getCartCount($conn, $user_id);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function addToCart($conn, $user_id) {
    $product_id = $_POST['product_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Product ID required']);
        return;
    }
    
    // Check if product already exists in cart
    $check_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing cart item
        $cart_item = $result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        $update_sql = "UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cart updated', 'quantity' => $new_quantity]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update cart']);
        }
    } else {
        // Add new cart item
        $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
        
        if ($insert_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Item added to cart']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add item to cart']);
        }
    }
}

function updateCartItem($conn, $user_id) {
    $cart_id = $_POST['cart_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$cart_id || $quantity < 1) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid cart ID or quantity']);
        return;
    }
    
    // Verify cart item belongs to user
    $update_sql = "UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    
    if ($update_stmt->execute() && $update_stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Cart item not found']);
    }
}

function removeFromCart($conn, $user_id) {
    $cart_id = $_POST['cart_id'] ?? 0;
    
    if (!$cart_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Cart ID required']);
        return;
    }
    
    $delete_sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $cart_id, $user_id);
    
    if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Cart item not found']);
    }
}

function getCartCount($conn, $user_id) {
    $count_sql = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $result = $count_stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    echo json_encode(['count' => $count]);
}
?> 