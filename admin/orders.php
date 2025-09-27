<?php
require_once 'includes/admin_auth.php';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($new_status, $valid_statuses)) {
        $sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $new_status, $order_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Order status updated successfully';
            logAdminAction('update_order_status', "Updated order #$order_id to $new_status");
        } else {
            $_SESSION['error'] = 'Failed to update order status';
        }
    }
    
    header('Location: orders.php');
    exit;
}

// Get orders with simple query
try {
    $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT 20";
    $result = $conn->query($sql);
    $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    $orders = [];
    $_SESSION['error'] = 'Error loading orders: ' . $e->getMessage();
}

// Get total count
try {
    $count_sql = "SELECT COUNT(*) as total FROM orders";
    $count_result = $conn->query($count_sql);
    $total_orders = $count_result ? $count_result->fetch_assoc()['total'] : 0;
} catch (Exception $e) {
    $total_orders = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin Panel</title>
    <style>
        /* Use the same styles as dashboard */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
        }
        
        .sidebar-header h2 {
            color: #3498db;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            color: #bdc3c7;
            font-size: 0.9em;
        }
        
        .sidebar-nav ul {
            list-style: none;
            margin-top: 30px;
        }
        
        .sidebar-nav li {
            margin-bottom: 10px;
        }
        
        .sidebar-nav a {
            color: #bdc3c7;
            text-decoration: none;
            padding: 10px;
            display: block;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: #34495e;
            color: white;
        }
        
        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid #34495e;
        }
        
        .logout-btn {
            color: #e74c3c;
            text-decoration: none;
            font-size: 0.9em;
        }
        
        .admin-main {
            flex: 1;
            padding: 20px;
        }
        
        .admin-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            color: #2c3e50;
        }
        
        .admin-info {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .content-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h3 {
            color: #2c3e50;
        }
        
        .card-content {
            padding: 20px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .status-select {
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .btn-small {
            background: #3498db;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.8em;
        }
        
        .no-data {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 40px;
        }
        
        .notification {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .notification-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .notification-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-pending { color: #f39c12; font-weight: 600; }
        .status-processing { color: #3498db; font-weight: 600; }
        .status-shipped { color: #9b59b6; font-weight: 600; }
        .status-delivered { color: #27ae60; font-weight: 600; }
        .status-cancelled { color: #e74c3c; font-weight: 600; }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h2>TechStore Pro</h2>
                <p>Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">📊 Dashboard</a></li>
                    <li><a href="products.php">📦 Products</a></li>
                    <li><a href="orders.php" class="active">📋 Orders</a></li>
                    <li><a href="users.php">👥 Users</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <p>Welcome, <?php echo htmlspecialchars($admin_name); ?></p>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="admin-main">
            <header class="admin-header">
                <h1>Orders Management</h1>
                <div class="header-actions">
                    <span class="admin-info"><?php echo number_format($total_orders); ?> orders</span>
                </div>
            </header>
            
            <div class="admin-content">
                <!-- Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="notification notification-success">
                        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="notification notification-error">
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Orders Table -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Orders (<?php echo number_format($total_orders); ?> total)</h3>
                    </div>
                    <div class="card-content">
                        <?php if (empty($orders)): ?>
                            <p class="no-data">No orders found</p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Unknown'); ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></td>
                                            <td>$<?php echo number_format($order['total'], 2); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="status" onchange="this.form.submit()" class="status-select">
                                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="#" class="btn-small">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
