<?php
require_once '../includes/auth_functions.php';

// Redirect if already logged in
redirectIfLoggedIn();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']);

        // Basic validation
        if (empty($email) || empty($password)) {
            throw new Exception('Please fill in all required fields.');
        }

        if (!isValidEmail($email)) {
            throw new Exception('Please enter a valid email address.');
        }

        // Check user credentials
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role, is_active FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new Exception('Invalid email or password.');
        }

        if (!$user['is_active']) {
            throw new Exception('Your account has been deactivated. Please contact support.');
        }

        // Login successful - create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();

        // Set remember me cookie if requested
        if ($remember_me) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60); // 30 days
            
            // Store token in database
            $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)");
            $stmt->execute([$user['id'], hash('sha256', $token), date('Y-m-d H:i:s', $expires)]);
            
            // Set cookie
            setcookie('remember_token', $token, $expires, '/', '', true, true);
        }

        // Update last login
        $stmt = $pdo->prepare("UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$user['id']]);

        $response['success'] = true;
        $response['message'] = 'Login successful! Redirecting...';
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
    header('Location: ../login.html');
}
exit;
?>
