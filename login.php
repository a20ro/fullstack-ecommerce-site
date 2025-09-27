<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/dashboard.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'customer';
    
    if (!empty($email) && !empty($password)) {
        require_once 'config/database.php';
        
        if ($user_type === 'admin') {
            // Admin login
            $sql = "SELECT * FROM admins WHERE username = ? AND status = 'active'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                
                if (password_verify($password, $admin['password'])) {
                    // Admin login successful
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_role'] = $admin['role'];
                    
                    // Log admin login
                    $log_sql = "INSERT INTO admin_logs (admin_id, action, details, ip_address, created_at) 
                               VALUES (?, 'login', 'Admin logged in', ?, NOW())";
                    $log_stmt = $conn->prepare($log_sql);
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
                    $log_stmt->bind_param("is", $admin['id'], $ip_address);
                    $log_stmt->execute();
                    
                    header('Location: admin/dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid username or password';
                }
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            // Customer login
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    // Customer login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['name'];
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid email or password';
                }
            } else {
                $error = 'Invalid email or password';
            }
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TechStore Pro</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 40px;
            text-align: center;
        }
        
        .login-header h1 {
            color: #2c3e50;
            font-size: 2em;
            margin-bottom: 5px;
        }
        
        .login-header h2 {
            color: #7f8c8d;
            font-size: 1.2em;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #95a5a6;
            margin-bottom: 30px;
        }
        
        .error-message {
            background: #e74c3c;
            color: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        
        .login-form .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .login-form input,
        .login-form select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }
        
        .login-form input:focus,
        .login-form select:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            background: linear-gradient(135deg, #2980b9, #1f5f8b);
            transform: translateY(-2px);
        }
        
        .login-footer {
            margin-top: 20px;
        }
        
        .login-footer a {
            color: #3498db;
            text-decoration: none;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .user-type-toggle {
            display: flex;
            background: #f8f9fa;
            border-radius: 6px;
            padding: 4px;
            margin-bottom: 20px;
        }
        
        .user-type-toggle button {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .user-type-toggle button.active {
            background: #3498db;
            color: white;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>TechStore Pro</h1>
                <h2>Login</h2>
                <p>Sign in to your account</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="user-type-toggle">
                    <button type="button" class="user-type-btn active" data-type="customer">Customer</button>
                    <button type="button" class="user-type-btn" data-type="admin">Admin</button>
                </div>
                
                <input type="hidden" name="user_type" id="user_type" value="customer">
                
                <div class="form-group">
                    <label for="email" id="email_label">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">Sign In</button>
            </form>
            
            <div class="login-footer">
                <p><a href="index.php">← Back to Store</a></p>
                <p>Don't have an account? <a href="#" onclick="window.simpleFramework.openAuthModal()">Sign up</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Handle user type toggle
        const userTypeBtns = document.querySelectorAll('.user-type-btn');
        const userTypeInput = document.getElementById('user_type');
        const emailLabel = document.getElementById('email_label');
        const emailInput = document.getElementById('email');
        
        userTypeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                userTypeBtns.forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update hidden input and email field
                const userType = this.getAttribute('data-type');
                userTypeInput.value = userType;
                
                if (userType === 'admin') {
                    emailLabel.textContent = 'Username';
                    emailInput.type = 'text';
                    emailInput.placeholder = 'Enter your username';
                } else {
                    emailLabel.textContent = 'Email Address';
                    emailInput.type = 'email';
                    emailInput.placeholder = 'Enter your email';
                }
            });
        });
    </script>
</body>
</html>
