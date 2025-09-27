<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Website</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/item.css">
    <link rel="stylesheet" href="assets/css/profile.css">
    <link rel="stylesheet" href="assets/css/cart.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div id="sidebar-container"></div>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Page Header -->
            <header class="page-header">
                <a href="#" data-page="home" class="store-name">TechStore Pro</a>
                <div class="header-actions">
                    <span>🛒 Cart (0)</span>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span>👤 Profile</span>
                    <?php else: ?>
                        <a href="login.php" style="color: white; text-decoration: none;">🔑 Login</a>
                    <?php endif; ?>
                </div>
            </header>
            
            <div id="page-content">
                <!-- Page content will be loaded here -->
                <h1>Welcome to TechStore Pro</h1>
                <p>This is the main content area. Pages will be loaded here dynamically.</p>
            </div>
            
            <!-- Footer -->
            <div id="footer-container"></div>
        </main>
    </div>
    
    <!-- Authentication Modal -->
    <div id="auth-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeAuthModal()">&times;</span>
            
            <!-- Login Form -->
            <div id="login-form" class="auth-form">
                <h2>Sign In</h2>
                <form id="loginForm">
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Sign In</button>
                </form>
                <p class="auth-switch">
                    Don't have an account? <a href="#" onclick="showSignupForm()">Sign Up</a>
                </p>
            </div>
            
            <!-- Signup Form -->
            <div id="signup-form" class="auth-form" style="display: none;">
                <h2>Sign Up</h2>
                <form id="signupForm">
                    <div class="form-group">
                        <label for="signup-name">Full Name</label>
                        <input type="text" id="signup-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="signup-email">Email</label>
                        <input type="email" id="signup-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="signup-location">Location</label>
                        <input type="text" id="signup-location" name="location">
                    </div>
                    <div class="form-group">
                        <label for="signup-phone">Phone</label>
                        <input type="tel" id="signup-phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="signup-password">Password</label>
                        <input type="password" id="signup-password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="signup-confirm-password">Confirm Password</label>
                        <input type="password" id="signup-confirm-password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Sign Up</button>
                </form>
                <p class="auth-switch">
                    Already have an account? <a href="#" onclick="showLoginForm()">Sign In</a>
                </p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/framework.js"></script>
    <script src="assets/js/sidebar.js"></script>
    <script src="assets/js/footer.js"></script>
</body>
</html> 