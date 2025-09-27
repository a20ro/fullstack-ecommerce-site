<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- Debug: Starting admin_auth.php -->";

// Admin authentication check
session_start();

echo "<!-- Debug: Session started -->";
echo "<!-- Debug: Session data: " . print_r($_SESSION, true) . " -->";

// Check if admin is logged in
echo "<!-- Debug: Checking admin login status -->";
echo "<!-- Debug: admin_logged_in = " . (isset($_SESSION['admin_logged_in']) ? $_SESSION['admin_logged_in'] : 'NOT SET') . " -->";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<!-- Debug: Not logged in, redirecting to index.php -->";
    header('Location: index.php');
    exit;
}

echo "<!-- Debug: Admin is logged in, continuing -->";

// Get admin info
$admin_id = $_SESSION['admin_id'] ?? 0;
$admin_username = $_SESSION['admin_username'] ?? '';
$admin_name = $_SESSION['admin_name'] ?? '';
$admin_role = $_SESSION['admin_role'] ?? '';

// Database connection
echo "<!-- Debug: Loading database config -->";
try {
    require_once '../config/database.php';
    echo "<!-- Debug: Database config loaded successfully -->";
    echo "<!-- Debug: Database connection: " . ($conn ? 'SUCCESS' : 'FAILED') . " -->";
} catch (Exception $e) {
    echo "<!-- Debug: Error loading database config: " . $e->getMessage() . " -->";
    die("Database Error: " . $e->getMessage());
}

// Function to check admin permissions
function hasPermission($required_role) {
    global $admin_role;
    
    $role_hierarchy = [
        'super_admin' => 3,
        'admin' => 2,
        'moderator' => 1
    ];
    
    $user_level = $role_hierarchy[$admin_role] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

// Function to log admin actions
function logAdminAction($action, $details = '') {
    global $conn, $admin_id;
    
    $sql = "INSERT INTO admin_logs (admin_id, action, details, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt->bind_param("isss", $admin_id, $action, $details, $ip_address);
    $stmt->execute();
}
?>
