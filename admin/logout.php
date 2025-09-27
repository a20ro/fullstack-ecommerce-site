<?php
session_start();

// Log admin action before logout
if (isset($_SESSION['admin_id'])) {
    require_once '../config/database.php';
    
    $admin_id = $_SESSION['admin_id'];
    $sql = "INSERT INTO admin_logs (admin_id, action, details, ip_address, created_at) 
            VALUES (?, 'logout', 'Admin logged out', ?, NOW())";
    $stmt = $conn->prepare($sql);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt->bind_param("is", $admin_id, $ip_address);
    $stmt->execute();
}

// Destroy admin session
session_destroy();

// Redirect to admin login
header('Location: index.php');
exit;
?>
