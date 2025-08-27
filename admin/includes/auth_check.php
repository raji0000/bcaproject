<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.html?redirect=admin');
    exit;
}

// Get admin user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin'");
$stmt->execute([$_SESSION['user_id']]);
$admin_user = $stmt->fetch();

if (!$admin_user) {
    session_destroy();
    header('Location: ../login.html');
    exit;
}

// Function to get dashboard stats
function getDashboardStats($pdo) {
    $stats = [];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Total courses
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM courses");
    $stats['total_courses'] = $stmt->fetch()['count'];
    
    // Total challenges
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM challenges");
    $stats['total_challenges'] = $stmt->fetch()['count'];
    
    // Total submissions
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM submissions WHERE is_correct = 1");
    $stats['total_solves'] = $stmt->fetch()['count'];
    
    // New users this month
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $stats['new_users_month'] = $stmt->fetch()['count'];
    
    // Contact messages
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)");
    $stats['new_messages'] = $stmt->fetch()['count'];
    
    return $stats;
}

// Function to format numbers
function formatNumber($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return $number;
}
?>
