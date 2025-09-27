<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- Debug: Starting dashboard.php -->";

try {
    require_once 'includes/admin_auth.php';
    echo "<!-- Debug: admin_auth.php loaded successfully -->";
} catch (Exception $e) {
    echo "<!-- Debug: Error loading admin_auth.php: " . $e->getMessage() . " -->";
    die("Error: " . $e->getMessage());
}

// Get dashboard statistics
$stats = [];

try {
    // Total products
    $sql = "SELECT COUNT(*) as count FROM products";
    $result = $conn->query($sql);
    if ($result) {
        $stats['total_products'] = $result->fetch_assoc()['count'];
        echo "<!-- Debug: Products count: " . $stats['total_products'] . " -->";
    } else {
        echo "<!-- Debug: Error getting products count: " . $conn->error . " -->";
        $stats['total_products'] = 0;
    }
} catch (Exception $e) {
    echo "<!-- Debug: Exception getting products: " . $e->getMessage() . " -->";
    $stats['total_products'] = 0;
}

// Total orders
$sql = "SELECT COUNT(*) as count FROM orders";
$result = $conn->query($sql);
$stats['total_orders'] = $result->fetch_assoc()['count'];

// Total users
$sql = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($sql);
$stats['total_users'] = $result->fetch_assoc()['count'];

// Total revenue
$sql = "SELECT SUM(total) as total FROM orders WHERE status != 'cancelled'";
$result = $conn->query($sql);
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Recent orders
$sql = "SELECT o.*, u.name as customer_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 10";
$recent_orders = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Recent users
$sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Low stock products
$sql = "SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5";
$low_stock_products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        /* Fallback styles if external CSS doesn't load */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            font-size: 2em;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #3498db;
            color: white;
            border-radius: 50%;
        }
        
        .stat-info h3 {
            font-size: 2em;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .content-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        .view-all {
            color: #3498db;
            text-decoration: none;
            font-size: 0.9em;
        }
        
        .card-content {
            padding: 20px;
        }
        
        .table-responsive {
            overflow-x: auto;
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
        
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-shipped {
            background: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .stock-low {
            color: #e74c3c;
            font-weight: 600;
        }
        
        .no-data {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 40px;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
                    <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                    <li><a href="products.php">📦 Products</a></li>
                    <li><a href="orders.php">📋 Orders</a></li>
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
                <h1>Dashboard</h1>
                <div class="header-actions">
                    <span class="admin-info"><?php echo htmlspecialchars($admin_name); ?> (<?php echo ucfirst($admin_role); ?>)</span>
                </div>
            </header>
            
            <div class="admin-content">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📦</div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_products']); ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_orders']); ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_users']); ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-info">
                            <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>
                
                <!-- Content Grid -->
                <div class="content-grid">
                    <!-- Recent Orders -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3>Recent Orders</h3>
                            <a href="orders.php" class="view-all">View All</a>
                        </div>
                        <div class="card-content">
                            <?php if (empty($recent_orders)): ?>
                                <p class="no-data">No orders found</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_orders as $order): ?>
                                                <tr>
                                                    <td>#<?php echo $order['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                    <td>$<?php echo number_format($order['total'], 2); ?></td>
                                                    <td>
                                                        <span class="status status-<?php echo $order['status']; ?>">
                                                            <?php echo ucfirst($order['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Low Stock Products -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3>Low Stock Products</h3>
                            <a href="products.php" class="view-all">View All</a>
                        </div>
                        <div class="card-content">
                            <?php if (empty($low_stock_products)): ?>
                                <p class="no-data">All products are well stocked</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Stock</th>
                                                <th>Price</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($low_stock_products as $product): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                    <td>
                                                        <span class="stock-low"><?php echo $product['stock']; ?></span>
                                                    </td>
                                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                                    <td>
                                                        <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn-small">Edit</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>
