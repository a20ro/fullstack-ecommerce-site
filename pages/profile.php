<?php
require_once __DIR__ . '/../config/database.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    // Get user information
    $userId = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
?>

<div class="profile-container">
    <?php if ($isLoggedIn && $user): ?>
        <!-- User Profile Section -->
        <div class="profile-content">
            <h1>My Profile</h1>
            <div class="profile-info">
                <div class="profile-avatar">
                    <img src="assets/images/profile-logo.svg" alt="Profile Avatar" class="avatar-img">
                </div>
                <div class="profile-details">
                    <form id="profile-form" class="profile-form">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($user['location']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password (leave blank to keep current)</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                            <a href="index.php?page=order_history" class="btn btn-secondary">View Order History</a>
                            <button type="button" class="btn btn-secondary" onclick="framework.logout()">Logout</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Login/Signup Modal Trigger -->
        <div class="profile-login-prompt">
            <h1>Welcome to TechStore Pro</h1>
            <p>Please sign in to access your profile and manage your account.</p>
            <button class="btn btn-primary" onclick="openAuthModal()">Sign In / Sign Up</button>
        </div>
    <?php endif; ?>
</div>

<!-- Authentication Modal -->
<div id="auth-modal" class="modal">
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


