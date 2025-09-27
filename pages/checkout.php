<?php
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<div class="checkout-page">
            <h1>Checkout</h1>
            <div class="checkout-error">
                <p>Please log in to proceed with checkout.</p>
                <a href="#" class="btn-primary" onclick="window.simpleFramework.openAuthModal()">Log in</a>
            </div>
          </div>';
    return;
}

$user_id = $_SESSION['user_id'];

// Get user details
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Get cart items with product details
$cart_sql = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.description, p.price, p.image 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? 
            ORDER BY c.created_at DESC";

$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

$cart_items = [];
$subtotal = 0;

if ($cart_result && $cart_result->num_rows > 0) {
    while ($row = $cart_result->fetch_assoc()) {
        $cart_items[] = $row;
        $subtotal += $row['price'] * $row['quantity'];
    }
}

// If cart is empty, redirect to cart page
if (empty($cart_items)) {
    echo '<div class="checkout-page">
            <h1>Checkout</h1>
            <div class="checkout-error">
                <p>Your cart is empty. Add some items before checkout.</p>
                <a href="index.php?page=products" class="btn-primary">Browse Products</a>
            </div>
          </div>';
    return;
}

$shipping = 0; // Free shipping
$tax = $subtotal * 0.08; // 8% tax
$total = $subtotal + $shipping + $tax;
?>

<div class="checkout-page">
    <h1>Checkout</h1>
    
    <div class="checkout-container">
        <div class="checkout-form-section">
            <form id="checkout-form" class="checkout-form">
                <!-- Shipping Information -->
                <div class="form-section">
                    <h2>Shipping Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="shipping_name">Full Name *</label>
                            <input type="text" id="shipping_name" name="shipping_name" 
                                   value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="shipping_email">Email *</label>
                            <input type="email" id="shipping_email" name="shipping_email" 
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="shipping_phone">Phone *</label>
                            <input type="tel" id="shipping_phone" name="shipping_phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="shipping_address">Address *</label>
                            <input type="text" id="shipping_address" name="shipping_address" 
                                   placeholder="Street address" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="shipping_city">City *</label>
                            <input type="text" id="shipping_city" name="shipping_city" required>
                        </div>
                        <div class="form-group">
                            <label for="shipping_state">State *</label>
                            <input type="text" id="shipping_state" name="shipping_state" required>
                        </div>
                        <div class="form-group">
                            <label for="shipping_zip">ZIP Code *</label>
                            <input type="text" id="shipping_zip" name="shipping_zip" required>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="form-section">
                    <h2>Payment Information</h2>
                    <div class="form-group">
                        <label for="card_number">Card Number *</label>
                        <input type="text" id="card_number" name="card_number" 
                               placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date *</label>
                            <input type="text" id="expiry_date" name="expiry_date" 
                                   placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV *</label>
                            <input type="text" id="cvv" name="cvv" 
                                   placeholder="123" maxlength="4" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="card_name">Name on Card *</label>
                        <input type="text" id="card_name" name="card_name" 
                               value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>
                </div>

                <!-- Billing Address -->
                <div class="form-section">
                    <h2>Billing Address</h2>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="same_as_shipping" checked>
                            <span class="checkmark"></span>
                            Same as shipping address
                        </label>
                    </div>
                    
                    <div id="billing-address" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="billing_name">Full Name *</label>
                                <input type="text" id="billing_name" name="billing_name">
                            </div>
                            <div class="form-group">
                                <label for="billing_address">Address *</label>
                                <input type="text" id="billing_address" name="billing_address">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="billing_city">City *</label>
                                <input type="text" id="billing_city" name="billing_city">
                            </div>
                            <div class="form-group">
                                <label for="billing_state">State *</label>
                                <input type="text" id="billing_state" name="billing_state">
                            </div>
                            <div class="form-group">
                                <label for="billing_zip">ZIP Code *</label>
                                <input type="text" id="billing_zip" name="billing_zip">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="window.simpleFramework.loadPage('cart')">
                        ← Back to Cart
                    </button>
                    <button type="submit" class="btn-primary checkout-submit">
                        Complete Order - $<?php echo number_format($total, 2); ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="order-summary-section">
            <div class="order-summary">
                <h2>Order Summary</h2>
                
                <div class="order-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p>Quantity: <?php echo $item['quantity']; ?></p>
                                <p class="item-price">$<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <div class="item-total">
                                $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div class="total-row">
                        <span>Tax:</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="total-row total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.checkout-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
    margin-top: 30px;
}

.checkout-form-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e9ecef;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section h2 {
    color: #2c3e50;
    margin-bottom: 25px;
    font-size: 1.5em;
    font-weight: 600;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-row:has(.form-group:nth-child(3)) {
    grid-template-columns: 1fr 1fr 1fr;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 600;
    font-size: 0.9em;
}

.form-group input {
    padding: 12px 16px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 1em;
    transition: border-color 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 500;
}

.checkbox-label input[type="checkbox"] {
    margin-right: 10px;
    transform: scale(1.2);
}

.form-actions {
    display: flex;
    gap: 20px;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #e9ecef;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #7f8c8d;
    transform: translateY(-2px);
}

.btn-primary {
    background: linear-gradient(135deg, #27ae60, #229954);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #229954, #1e8449);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.order-summary-section {
    position: sticky;
    top: 20px;
    height: fit-content;
}

.order-summary {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.order-summary h2 {
    color: #2c3e50;
    margin-bottom: 25px;
    font-size: 1.5em;
    font-weight: 600;
}

.order-items {
    margin-bottom: 25px;
}

.order-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f1f3f4;
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-details h4 {
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 0.9em;
}

.item-details p {
    color: #7f8c8d;
    font-size: 0.8em;
    margin-bottom: 3px;
}

.item-price {
    color: #3498db !important;
    font-weight: 600;
}

.item-total {
    font-weight: 600;
    color: #2c3e50;
    align-self: center;
}

.order-totals {
    border-top: 2px solid #e9ecef;
    padding-top: 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #7f8c8d;
}

.total-row.total {
    font-size: 1.2em;
    font-weight: 600;
    color: #2c3e50;
    border-top: 1px solid #e9ecef;
    padding-top: 10px;
    margin-top: 10px;
}

.checkout-error {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.checkout-error h2 {
    color: #e74c3c;
    margin-bottom: 15px;
}

.checkout-error p {
    color: #7f8c8d;
    margin-bottom: 25px;
    font-size: 1.1em;
}

/* Responsive Design */
@media (max-width: 768px) {
    .checkout-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-row:has(.form-group:nth-child(3)) {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .order-summary-section {
        position: static;
    }
}
</style>
