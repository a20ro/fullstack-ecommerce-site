<?php
require_once __DIR__ . '/../config/database.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

// Get the action from POST data
$action = $_POST['action'] ?? '';

// Debug logging
error_log("Auth handler called with action: " . $action);
error_log("POST data: " . print_r($_POST, true));

switch ($action) {
    case 'signup':
        handleSignup();
        break;
    case 'login':
        handleLogin();
        break;
    case 'update_profile':
        handleUpdateProfile();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
        break;
}

function handleSignup() {
    global $conn;
    
    try {
        // Validate required fields
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        error_log("Signup attempt - Name: $name, Email: $email, Location: $location, Phone: $phone");
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Name, email, and password are required']);
            return;
        }
        
        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            return;
        }
        
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            return;
        }
        
        // Check if email already exists
        $checkSql = "SELECT id FROM users WHERE email = ?";
        $checkStmt = $conn->prepare($checkSql);
        if (!$checkStmt) {
            error_log("Prepare statement failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            return;
        }
        
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO users (name, email, password, location, phone, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare statement failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            return;
        }
        
        $stmt->bind_param("sssss", $name, $email, $hashedPassword, $location, $phone);
        
        if ($stmt->execute()) {
            error_log("User created successfully: $email");
            $response = json_encode(['success' => true, 'message' => 'Account created successfully']);
            error_log("Sending response: " . $response);
            echo $response;
        } else {
            error_log("Execute failed: " . $stmt->error);
            $response = json_encode(['success' => false, 'message' => 'Error creating account: ' . $stmt->error]);
            error_log("Sending response: " . $response);
            echo $response;
        }
        
    } catch (Exception $e) {
        error_log("Signup exception: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
}

function handleLogin() {
    global $conn;
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        return;
    }
    
    // Get user by email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        return;
    }
    
    $user = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        
        echo json_encode(['success' => true, 'message' => 'Login successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
}

function handleUpdateProfile() {
    global $conn;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        return;
    }
    
    $userId = $_SESSION['user_id'];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Name and email are required']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    // Check if email is already taken by another user
    $checkSql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $email, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        return;
    }
    
    // Build update query
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
            return;
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name = ?, email = ?, location = ?, phone = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $email, $location, $phone, $hashedPassword, $userId);
    } else {
        $sql = "UPDATE users SET name = ?, email = ?, location = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $location, $phone, $userId);
    }
    
    if ($stmt->execute()) {
        // Update session data
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name;
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating profile']);
    }
}

function handleLogout() {
    // Destroy session
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}
?> 