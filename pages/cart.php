<?php
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<div class="cart-page">
            <h1>Your Shopping Cart</h1>
            <div class="cart-empty">
                <p>Please log in to view your cart.</p>
                <a href="login.php" class="btn-primary">Log in</a>
            </div>
          </div>';
    return;
}

$user_id = $_SESSION['user_id'];

// Get cart items with product details
$sql = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.description, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
}
?>

<div class="cart-page">
    <h1>Your Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <div class="cart-empty">
            <h2>Your cart is empty</h2>
            <p>Start shopping to add items to your cart!</p>
            <a href="index.php?page=products" class="btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="cart-container">
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>" data-product-id="<?php echo $item['product_id']; ?>">
                        <div class="cart-item-image">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="cart-item-price">$<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <div class="cart-item-quantity">
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn" data-cart-id="<?php echo $item['cart_id']; ?>" data-change="-1">-</button>
                                <span class="quantity-display" id="cart-quantity-<?php echo $item['cart_id']; ?>"><?php echo $item['quantity']; ?></span>
                                <button type="button" class="quantity-btn" data-cart-id="<?php echo $item['cart_id']; ?>" data-change="1">+</button>
                            </div>
                        </div>
                        <div class="cart-item-total">
                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                        <div class="cart-item-actions">
                            <button class="remove-item-btn" data-cart-id="<?php echo $item['cart_id']; ?>">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                <button class="checkout-btn" onclick="window.simpleFramework.loadPage('checkout')">Proceed to Checkout</button>
                <a href="index.php?page=products" class="continue-shopping">Continue Shopping</a>
            </div>
        </div>
    <?php endif; ?>
</div> 