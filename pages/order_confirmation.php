<?php
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<div class="order-confirmation-page">
            <h1>Order Confirmation</h1>
            <div class="confirmation-error">
                <p>Please log in to view your order confirmation.</p>
                <a href="#" class="btn-primary" onclick="window.simpleFramework.openAuthModal()">Log in</a>
            </div>
          </div>';
    return;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    echo '<div class="order-confirmation-page">
            <h1>Order Confirmation</h1>
            <div class="confirmation-error">
                <p>No order ID provided.</p>
                <a href="index.php?page=home" class="btn-primary">Go Home</a>
            </div>
          </div>';
    return;
}

// Get order details
$order_sql = "SELECT o.*, u.name as user_name, u.email as user_email 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.id = ? AND o.user_id = ?";

$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    echo '<div class="order-confirmation-page">
            <h1>Order Confirmation</h1>
            <div class="confirmation-error">
                <p>Order not found or you do not have permission to view this order.</p>
                <a href="index.php?page=home" class="btn-primary">Go Home</a>
            </div>
          </div>';
    return;
}

$order = $order_result->fetch_assoc();

// Get order items
$items_sql = "SELECT * FROM order_items WHERE order_id = ? ORDER BY id";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$order_items = [];
while ($row = $items_result->fetch_assoc()) {
    $order_items[] = $row;
}
?>

<div class="order-confirmation-page">
    <div class="confirmation-header">
        <div class="success-icon">✅</div>
        <h1>Order Confirmed!</h1>
        <p class="confirmation-message">Thank you for your order. We've received your order and will process it shortly.</p>
    </div>
    
    <div class="confirmation-container">
        <div class="order-details-section">
            <h2>Order Details</h2>
            
            <div class="order-info">
                <div class="info-row">
                    <span class="label">Order Number:</span>
                    <span class="value">#<?php echo $order['id']; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Order Date:</span>
                    <span class="value"><?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="value status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Total Amount:</span>
                    <span class="value total-amount">$<?php echo number_format($order['total'], 2); ?></span>
                </div>
            </div>
            
            <div class="shipping-info">
                <h3>Shipping Information</h3>
                <div class="address-block">
                    <p><strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong></p>
                    <p><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                    <p><?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state']); ?> <?php echo htmlspecialchars($order['shipping_zip']); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                    <p>Email: <?php echo htmlspecialchars($order['shipping_email']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="order-items-section">
            <h2>Order Items</h2>
            
            <div class="order-items-list">
                <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <div class="item-details">
                            <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                            <p class="item-price">$<?php echo number_format($item['price'], 2); ?> each</p>
                        </div>
                        <div class="item-total">
                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($order['subtotal'], 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>$<?php echo number_format($order['shipping'], 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax:</span>
                    <span>$<?php echo number_format($order['tax'], 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($order['total'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="confirmation-actions">
        <a href="index.php?page=home" class="btn-primary">Continue Shopping</a>
        <a href="index.php?page=profile" class="btn-secondary">View My Orders</a>
    </div>
</div>

<style>
.order-confirmation-page {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.confirmation-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 20px;
    background: linear-gradient(135deg, #27ae60, #229954);
    color: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
}

.success-icon {
    font-size: 4em;
    margin-bottom: 20px;
}

.confirmation-header h1 {
    font-size: 2.5em;
    margin-bottom: 15px;
    font-weight: 600;
}

.confirmation-message {
    font-size: 1.2em;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.confirmation-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
}

.order-details-section,
.order-items-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.order-details-section h2,
.order-items-section h2 {
    color: #2c3e50;
    margin-bottom: 25px;
    font-size: 1.5em;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 10px;
}

.order-info {
    margin-bottom: 30px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-row:last-child {
    border-bottom: none;
}

.label {
    color: #7f8c8d;
    font-weight: 500;
}

.value {
    color: #2c3e50;
    font-weight: 600;
}

.status-pending {
    color: #f39c12;
}

.status-processing {
    color: #3498db;
}

.status-shipped {
    color: #9b59b6;
}

.status-delivered {
    color: #27ae60;
}

.total-amount {
    color: #27ae60;
    font-size: 1.2em;
}

.shipping-info h3 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 1.2em;
}

.address-block {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.address-block p {
    margin-bottom: 5px;
    color: #2c3e50;
}

.order-items-list {
    margin-bottom: 25px;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f1f3f4;
}

.order-item:last-child {
    border-bottom: none;
}

.item-details h4 {
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 1em;
}

.item-details p {
    color: #7f8c8d;
    font-size: 0.9em;
    margin-bottom: 3px;
}

.item-price {
    color: #3498db !important;
    font-weight: 600;
}

.item-total {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1em;
}

.order-summary {
    border-top: 2px solid #e9ecef;
    padding-top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #7f8c8d;
}

.summary-row.total {
    font-size: 1.2em;
    font-weight: 600;
    color: #2c3e50;
    border-top: 1px solid #e9ecef;
    padding-top: 10px;
    margin-top: 10px;
}

.confirmation-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-top: 40px;
}

.btn-primary {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    text-decoration: none;
    padding: 15px 30px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2980b9, #1f5f8b);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.btn-secondary {
    background: #95a5a6;
    color: white;
    text-decoration: none;
    padding: 15px 30px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.btn-secondary:hover {
    background: #7f8c8d;
    transform: translateY(-2px);
}

.confirmation-error {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.confirmation-error h2 {
    color: #e74c3c;
    margin-bottom: 15px;
}

.confirmation-error p {
    color: #7f8c8d;
    margin-bottom: 25px;
    font-size: 1.1em;
}

/* Responsive Design */
@media (max-width: 768px) {
    .confirmation-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .confirmation-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .confirmation-header h1 {
        font-size: 2em;
    }
    
    .success-icon {
        font-size: 3em;
    }
}
</style>
