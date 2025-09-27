<?php
require_once __DIR__ . '/../config/database.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<div class="order-history-container">
            <h1>Order History</h1>
            <div class="login-prompt">
                <div class="login-prompt-icon">🔐</div>
                <h2>Access Your Order History</h2>
                <p>Please log in to view your order history and track your purchases.</p>
                <a href="login.php" class="btn-login">Log in to Continue</a>
                <div class="login-prompt-footer">
                    <p>Don\'t have an account? <a href="login.php" class="signup-link">Sign up here</a></p>
                </div>
            </div>
          </div>';
    return;
}

$user_id = $_SESSION['user_id'];

// Get user's orders with order items
try {
    $sql = "SELECT o.*, 
                   COUNT(oi.id) as item_count,
                   GROUP_CONCAT(oi.product_name SEPARATOR ', ') as product_names
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.user_id = ? 
            GROUP BY o.id 
            ORDER BY o.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $orders = [];
}
?>

<div class="order-history-container">
    <h1>My Order History</h1>
    
    <?php if (empty($orders)): ?>
        <div class="no-orders">
            <div class="no-orders-icon">📦</div>
            <h2>No orders yet</h2>
            <p>You haven't placed any orders yet. Start shopping to see your order history here!</p>
            <a href="index.php?page=products" class="btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Order #<?php echo $order['id']; ?></h3>
                            <p class="order-date">Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="order-status">
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="order-items">
                            <h4>Items (<?php echo $order['item_count']; ?>):</h4>
                            <p class="items-preview"><?php echo htmlspecialchars($order['product_names']); ?></p>
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
                    
                    <div class="order-actions">
                        <button class="btn-secondary" onclick="window.viewOrderDetails(<?php echo $order['id']; ?>)">
                            View Details
                        </button>
                        <?php if ($order['status'] === 'delivered'): ?>
                            <button class="btn-primary" onclick="window.reorderItems(<?php echo $order['id']; ?>)">
                                Reorder
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Order Details Modal -->
<div id="order-details-modal" class="modal">
    <div class="modal-content order-details-content">
        <span class="close" onclick="closeOrderDetails()">&times;</span>
        <div id="order-details-body">
            <!-- Order details will be loaded here -->
        </div>
    </div>
</div>

<style>
.order-history-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.order-history-container h1 {
    color: #2c3e50;
    margin-bottom: 30px;
    text-align: center;
}

.login-prompt {
    text-align: center;
    padding: 60px 40px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    border: 1px solid rgba(52, 152, 219, 0.1);
    max-width: 500px;
    margin: 40px auto;
    position: relative;
    overflow: hidden;
}

.login-prompt::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3498db, #2980b9, #3498db);
}

.login-prompt-icon {
    font-size: 4em;
    margin-bottom: 20px;
    opacity: 0.8;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.1); opacity: 1; }
}

.login-prompt h2 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 1.8em;
    font-weight: 600;
}

.login-prompt p {
    color: #7f8c8d;
    margin-bottom: 30px;
    font-size: 1.1em;
    line-height: 1.6;
}

.btn-login {
    display: inline-block;
    padding: 15px 30px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1.1em;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    margin-bottom: 25px;
}

.btn-login:hover {
    background: linear-gradient(135deg, #2980b9, #1f5f8b);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
    color: white;
    text-decoration: none;
}

.login-prompt-footer {
    border-top: 1px solid #ecf0f1;
    padding-top: 20px;
    margin-top: 20px;
}

.login-prompt-footer p {
    margin: 0;
    color: #95a5a6;
    font-size: 0.95em;
}

.signup-link {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.signup-link:hover {
    color: #2980b9;
    text-decoration: underline;
}

.no-orders {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.no-orders-icon {
    font-size: 4em;
    margin-bottom: 20px;
}

.no-orders h2 {
    color: #2c3e50;
    margin-bottom: 15px;
}

.no-orders p {
    color: #7f8c8d;
    margin-bottom: 30px;
    font-size: 1.1em;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    transition: transform 0.2s ease;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ecf0f1;
}

.order-info h3 {
    color: #2c3e50;
    margin-bottom: 5px;
}

.order-date {
    color: #7f8c8d;
    font-size: 0.9em;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending { background: #fef3cd; color: #856404; }
.status-processing { background: #d1ecf1; color: #0c5460; }
.status-shipped { background: #e2e3f1; color: #383d41; }
.status-delivered { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }

.order-details {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 20px;
}

.order-items h4 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.items-preview {
    color: #7f8c8d;
    line-height: 1.5;
}

.order-summary {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 0.9em;
}

.summary-row.total {
    font-weight: 600;
    font-size: 1em;
    padding-top: 8px;
    border-top: 1px solid #dee2e6;
    margin-top: 8px;
}

.order-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-primary, .btn-secondary {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.9em;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
}

.order-details-content {
    position: relative;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    position: absolute;
    right: 15px;
    top: 10px;
}

.close:hover {
    color: #000;
}

@media (max-width: 768px) {
    .order-details {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .order-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .order-actions {
        justify-content: center;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
    
    .login-prompt {
        padding: 40px 20px;
        margin: 20px auto;
        max-width: 90%;
    }
    
    .login-prompt-icon {
        font-size: 3em;
    }
    
    .login-prompt h2 {
        font-size: 1.5em;
    }
    
    .login-prompt p {
        font-size: 1em;
    }
    
    .btn-login {
        padding: 12px 25px;
        font-size: 1em;
    }
}
</style>

