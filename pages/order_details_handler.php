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
    // Get order details
    $order_sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param('ii', $order_id, $user_id);
    $order_stmt->execute();
    $order = $order_stmt->get_result()->fetch_assoc();
    
    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }
    
    // Get order items
    $items_sql = "SELECT * FROM order_items WHERE order_id = ? ORDER BY id";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param('i', $order_id);
    $items_stmt->execute();
    $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Generate HTML
    $html = '
    <div class="order-details-modal">
        <h2>Order #' . $order['id'] . ' Details</h2>
        
        <div class="order-info-grid">
            <div class="info-section">
                <h3>Order Information</h3>
                <div class="info-row">
                    <span class="label">Order Date:</span>
                    <span class="value">' . date('F j, Y \a\t g:i A', strtotime($order['created_at'])) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="value status-' . $order['status'] . '">' . ucfirst($order['status']) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Total:</span>
                    <span class="value">$' . number_format($order['total'], 2) . '</span>
                </div>
            </div>
            
            <div class="info-section">
                <h3>Shipping Information</h3>
                <div class="info-row">
                    <span class="label">Name:</span>
                    <span class="value">' . htmlspecialchars($order['shipping_name']) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value">' . htmlspecialchars($order['shipping_email']) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Phone:</span>
                    <span class="value">' . htmlspecialchars($order['shipping_phone']) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Address:</span>
                    <span class="value">' . htmlspecialchars($order['shipping_address']) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">City:</span>
                    <span class="value">' . htmlspecialchars($order['shipping_city']) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">State:</span>
                    <span class="value">' . htmlspecialchars($order['shipping_state']) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">ZIP:</span>
                    <span class="value">' . htmlspecialchars($order['shipping_zip']) . '</span>
                </div>
            </div>
        </div>
        
        <div class="order-items-section">
            <h3>Order Items</h3>
            <div class="items-table">
                <div class="table-header">
                    <div class="col-name">Product</div>
                    <div class="col-qty">Quantity</div>
                    <div class="col-price">Price</div>
                    <div class="col-total">Total</div>
                </div>';
    
    foreach ($items as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $html .= '
                <div class="table-row">
                    <div class="col-name">' . htmlspecialchars($item['product_name']) . '</div>
                    <div class="col-qty">' . $item['quantity'] . '</div>
                    <div class="col-price">$' . number_format($item['price'], 2) . '</div>
                    <div class="col-total">$' . number_format($item_total, 2) . '</div>
                </div>';
    }
    
    $html .= '
            </div>
        </div>
        
        <div class="order-summary-section">
            <h3>Order Summary</h3>
            <div class="summary-details">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$' . number_format($order['subtotal'], 2) . '</span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>$' . number_format($order['shipping'], 2) . '</span>
                </div>
                <div class="summary-row">
                    <span>Tax:</span>
                    <span>$' . number_format($order['tax'], 2) . '</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>$' . number_format($order['total'], 2) . '</span>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .order-details-modal h2 {
        color: #2c3e50;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .order-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .info-section h3 {
        color: #2c3e50;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #3498db;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding: 5px 0;
    }
    
    .info-row .label {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .info-row .value {
        color: #7f8c8d;
    }
    
    .status-pending { color: #f39c12; font-weight: 600; }
    .status-processing { color: #3498db; font-weight: 600; }
    .status-shipped { color: #9b59b6; font-weight: 600; }
    .status-delivered { color: #27ae60; font-weight: 600; }
    .status-cancelled { color: #e74c3c; font-weight: 600; }
    
    .order-items-section {
        margin-bottom: 30px;
    }
    
    .order-items-section h3 {
        color: #2c3e50;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #3498db;
    }
    
    .items-table {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        overflow: hidden;
    }
    
    .table-header {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .table-row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        border-top: 1px solid #dee2e6;
    }
    
    .table-header > div,
    .table-row > div {
        padding: 12px;
        border-right: 1px solid #dee2e6;
    }
    
    .table-header > div:last-child,
    .table-row > div:last-child {
        border-right: none;
    }
    
    .table-row:nth-child(even) {
        background: #f8f9fa;
    }
    
    .order-summary-section h3 {
        color: #2c3e50;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #3498db;
    }
    
    .summary-details {
        background: #f8f9fa;
        padding: 20px;
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
        font-size: 1.1em;
        padding-top: 10px;
        border-top: 2px solid #dee2e6;
        margin-top: 10px;
    }
    
    @media (max-width: 768px) {
        .order-info-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .table-header,
        .table-row {
            grid-template-columns: 1fr;
        }
        
        .table-header > div,
        .table-row > div {
            border-right: none;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table-header > div:last-child,
        .table-row > div:last-child {
            border-bottom: none;
        }
    }
    </style>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
