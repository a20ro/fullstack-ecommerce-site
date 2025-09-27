<?php
require_once 'includes/admin_auth.php';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'delete':
            deleteUser($conn);
            break;
        case 'toggle_status':
            toggleUserStatus($conn);
            break;
    }
}

// Get users with simple query
try {
    $search = $_GET['search'] ?? '';
    
    if (!empty($search)) {
        $sql = "SELECT * FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC LIMIT 20";
        $search_param = "%$search%";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $search_param, $search_param);
        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 20";
        $result = $conn->query($sql);
        $users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
} catch (Exception $e) {
    $users = [];
    $_SESSION['error'] = 'Error loading users: ' . $e->getMessage();
}

// Get total count
try {
    $count_sql = "SELECT COUNT(*) as total FROM users";
    $count_result = $conn->query($count_sql);
    $total_users = $count_result ? $count_result->fetch_assoc()['total'] : 0;
} catch (Exception $e) {
    $total_users = 0;
}

function deleteUser($conn) {
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    // Get user name for logging
    $name_sql = "SELECT name FROM users WHERE id = ?";
    $name_stmt = $conn->prepare($name_sql);
    $name_stmt->bind_param('i', $user_id);
    $name_stmt->execute();
    $user_name = $name_stmt->get_result()->fetch_assoc()['name'] ?? 'Unknown';
    
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'User deleted successfully';
        logAdminAction('delete_user', "Deleted user: $user_name");
    } else {
        $_SESSION['error'] = 'Failed to delete user';
    }
    
    header('Location: users_simple.php');
    exit;
}

function toggleUserStatus($conn) {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    
    $valid_statuses = ['active', 'inactive'];
    if (in_array($new_status, $valid_statuses)) {
        $sql = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $new_status, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'User status updated successfully';
            logAdminAction('toggle_user_status', "Updated user #$user_id to $new_status");
        } else {
            $_SESSION['error'] = 'Failed to update user status';
        }
    }
    
    header('Location: users_simple.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin Panel</title>
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
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 6px;
            font-size: 1em;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
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
        
        .btn-small {
            background: #3498db;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.8em;
            margin-right: 5px;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
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
        
        .status-active { color: #27ae60; font-weight: 600; }
        .status-inactive { color: #e74c3c; font-weight: 600; }
        
        .search-form {
            display: flex;
            gap: 10px;
            align-items: end;
        }
        
        .search-form .form-group {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
            }
            
            .form-row {
                flex-direction: column;
            }
            
            .search-form {
                flex-direction: column;
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
                    <li><a href="orders.php">📋 Orders</a></li>
                    <li><a href="users.php" class="active">👥 Users</a></li>
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
                <h1>Users Management</h1>
                <div class="header-actions">
                    <span class="admin-info"><?php echo number_format($total_users); ?> users</span>
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
                
                <!-- Search -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Search Users</h3>
                    </div>
                    <div class="card-content">
                        <form method="GET" class="search-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <input type="text" name="search" placeholder="Search by name or email..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <button type="submit" class="btn-secondary">Search</button>
                                <?php if (!empty($search)): ?>
                                    <a href="users.php" class="btn-secondary">Clear</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Users (<?php echo number_format($total_users); ?> total)</h3>
                    </div>
                    <div class="card-content">
                        <?php if (empty($users)): ?>
                            <p class="no-data">No users found</p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Location</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <?php
                                        // Get user's order count
                                        try {
                                            $order_count_sql = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
                                            $order_count_stmt = $conn->prepare($order_count_sql);
                                            $order_count_stmt->bind_param('i', $user['id']);
                                            $order_count_stmt->execute();
                                            $order_count = $order_count_stmt->get_result()->fetch_assoc()['count'];
                                        } catch (Exception $e) {
                                            $order_count = 0;
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                                <br>
                                                <small>Orders: <?php echo $order_count; ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['location'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="status-<?php echo $user['status'] ?? 'active'; ?>">
                                                    <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="btn-small btn-danger">Delete</button>
                                                </form>
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
