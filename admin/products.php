<?php
require_once 'includes/admin_auth.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            addProduct($conn);
            break;
        case 'edit':
            editProduct($conn);
            break;
        case 'delete':
            deleteProduct($conn);
            break;
    }
}

// Get products
try {
    $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 20";
    $result = $conn->query($sql);
    $products = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $products = [];
}

// Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    try {
        $edit_id = (int)$_GET['edit'];
        $edit_sql = "SELECT * FROM products WHERE id = ?";
        $edit_stmt = $conn->prepare($edit_sql);
        $edit_stmt->bind_param('i', $edit_id);
        $edit_stmt->execute();
        $edit_product = $edit_stmt->get_result()->fetch_assoc();
    } catch (Exception $e) {
        $edit_product = null;
    }
}

function addProduct($conn) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $image = trim($_POST['image'] ?? '');
    
    if (empty($name) || empty($description) || $price <= 0) {
        $_SESSION['error'] = 'Please fill in all required fields';
        return;
    }
    
    $sql = "INSERT INTO products (name, description, price, stock, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssdis', $name, $description, $price, $stock, $image);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Product added successfully';
        logAdminAction('add_product', "Added product: $name");
    } else {
        $_SESSION['error'] = 'Failed to add product';
    }
    
    header('Location: products_new.php');
    exit;
}

function editProduct($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $image = trim($_POST['image'] ?? '');
    
    if (empty($name) || empty($description) || $price <= 0) {
        $_SESSION['error'] = 'Please fill in all required fields';
        return;
    }
    
    $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssdisi', $name, $description, $price, $stock, $image, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Product updated successfully';
        logAdminAction('edit_product', "Updated product: $name");
    } else {
        $_SESSION['error'] = 'Failed to update product';
    }
    
    header('Location: products_new.php');
    exit;
}

function deleteProduct($conn) {
    $id = (int)($_POST['id'] ?? 0);
    
    // Get product name for logging
    $name_sql = "SELECT name FROM products WHERE id = ?";
    $name_stmt = $conn->prepare($name_sql);
    $name_stmt->bind_param('i', $id);
    $name_stmt->execute();
    $product_name = $name_stmt->get_result()->fetch_assoc()['name'] ?? 'Unknown';
    
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Product deleted successfully';
        logAdminAction('delete_product', "Deleted product: $product_name");
    } else {
        $_SESSION['error'] = 'Failed to delete product';
    }
    
    header('Location: products_new.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin Panel</title>
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
        
        .btn-primary {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary:hover {
            background: #2980b9;
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
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 6px;
            font-size: 1em;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
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
        
        .product-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .no-image {
            color: #95a5a6;
            font-style: italic;
        }
        
        .stock-low {
            color: #e74c3c;
            font-weight: 600;
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
                    <li><a href="products.php" class="active">📦 Products</a></li>
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
                <h1>Products Management</h1>
                <div class="header-actions">
                    <button class="btn-primary" onclick="toggleProductForm()">+ Add Product</button>
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
                
                <!-- Add/Edit Product Form -->
                <div class="content-card" id="product-form" style="display: <?php echo $edit_product ? 'block' : 'none'; ?>;">
                    <div class="card-header">
                        <h3><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h3>
                        <button type="button" onclick="toggleProductForm()" class="btn-secondary">Cancel</button>
                    </div>
                    <div class="card-content">
                        <form method="POST" class="product-form">
                            <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit' : 'add'; ?>">
                            <?php if ($edit_product): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Product Name *</label>
                                    <input type="text" id="name" name="name" required 
                                           value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="price">Price *</label>
                                    <input type="number" id="price" name="price" step="0.01" required 
                                           value="<?php echo $edit_product['price'] ?? ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="stock">Stock Quantity</label>
                                    <input type="number" id="stock" name="stock" min="0" 
                                           value="<?php echo $edit_product['stock'] ?? '0'; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="image">Image URL</label>
                                    <input type="url" id="image" name="image" 
                                           value="<?php echo htmlspecialchars($edit_product['image'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description *</label>
                                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">
                                    <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Products Table -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Products (<?php echo count($products); ?> total)</h3>
                    </div>
                    <div class="card-content">
                        <?php if (empty($products)): ?>
                            <p class="no-data">No products found</p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td>
                                                <?php if (!empty($product['image'])): ?>
                                                    <?php 
                                                    // Fix image path for admin directory
                                                    $image_path = $product['image'];
                                                    if (!str_starts_with($image_path, 'http') && !str_starts_with($image_path, '/')) {
                                                        $image_path = '../' . $image_path;
                                                    }
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                         class="product-thumb"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                                    <span class="no-image" style="display:none;">Image not found</span>
                                                <?php else: ?>
                                                    <span class="no-image">No Image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                                <br>
                                                <small><?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...</small>
                                            </td>
                                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                                            <td>
                                                <span class="<?php echo $product['stock'] < 10 ? 'stock-low' : ''; ?>">
                                                    <?php echo $product['stock']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                                            <td>
                                                <a href="products_new.php?edit=<?php echo $product['id']; ?>" class="btn-small">Edit</a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
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
    
    <script>
        function toggleProductForm() {
            const form = document.getElementById('product-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
