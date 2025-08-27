<?php
require_once '../includes/auth_functions.php';

// Redirect if already logged in
redirectIfLoggedIn();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $terms = isset($_POST['terms']);

        // Basic validation
        if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password)) {
            throw new Exception('Please fill in all required fields.');
        }

        if (!$terms) {
            throw new Exception('You must agree to the Terms of Service and Privacy Policy.');
        }

        // Validate email
        if (!isValidEmail($email)) {
            throw new Exception('Please enter a valid email address.');
        }

        // Validate username
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            throw new Exception('Username must be 3-20 characters long and contain only letters, numbers, and underscores.');
        }

        // Validate password
        if (!isValidPassword($password)) {
            throw new Exception('Password must be at least 8 characters long and contain uppercase, lowercase, and numbers.');
        }

        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match.');
        }

        // Check if email or username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            throw new Exception('Email or username already exists. Please choose different ones.');
        }

        // Create new user
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, role, created_at) VALUES (?, ?, ?, ?, ?, 'student', CURRENT_TIMESTAMP)");
        $stmt->execute([$username, $email, $password_hash, $first_name, $last_name]);
        
        $user_id = $pdo->lastInsertId();

        // Auto-login the user
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'student';
        $_SESSION['login_time'] = time();

        $response['success'] = true;
        $response['message'] = 'Account created successfully! Welcome to CyberSec Academy!';
        $response['redirect'] = 'dashboard.php';

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

// Return JSON response for AJAX requests
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// For non-AJAX requests, redirect with message
if ($response['success']) {
    header('Location: ../dashboard.php');
} else {
    $_SESSION['error_message'] = $response['message'];
    header('Location: ../register.html');
}
exit;
?>
