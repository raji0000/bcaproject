<?php
require_once '../includes/auth_functions.php';

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    // Remove token from database
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id'] ?? 0]);
    
    // Clear cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Destroy session
session_destroy();

// Redirect to home page
header('Location: ../index.html');
exit;
?>
