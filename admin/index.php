<?php
require_once 'includes/auth_check.php';
$stats = getDashboardStats($pdo);

// Get recent activity
$stmt = $pdo->prepare("
    SELECT u.username, c.title as challenge_title, s.solved_at, c.points
    FROM submissions s
    JOIN users u ON s.user_id = u.id
    JOIN challenges c ON s.challenge_id = c.id
    WHERE s.is_correct = 1
    ORDER BY s.solved_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_solves = $stmt->fetchAll();

// Get recent registrations
$stmt = $pdo->prepare("
    SELECT username, email, created_at
    FROM users
    WHERE role = 'student'
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute();
$recent_users = $stmt->fetchAll();

// Get contact messages
$stmt = $pdo->prepare("
    SELECT first_name, last_name, subject, created_at, status
    FROM contact_messages
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute();
$recent_messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CyberSec Platform</title>
    <link rel="stylesheet" href="../assets/css/globals.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700&family=Open+Sans:wght@400;500&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Admin Panel</h2>
            <p class="sidebar-subtitle">CyberSec Platform</p>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item active">
                <span class="nav-icon">📊</span>
                Dashboard
            </a>
            <a href="users.php" class="nav-item">
                <span class="nav-icon">👥</span>
                Users
            </a>
            <a href="courses.php" class="nav-item">
                <span class="nav-icon">📚</span>
                Courses
            </a>
            <a href="challenges.php" class="nav-item">
                <span class="nav-icon">🏆</span>
                Challenges
            </a>
            <a href="messages.php" class="nav-item">
                <span class="nav-icon">💬</span>
                Messages
            </a>
            <a href="../index.html" class="nav-item">
                <span class="nav-icon">🌐</span>
                View Site
            </a>
            <a href="../auth/logout.php" class="nav-item logout">
                <span class="nav-icon">🚪</span>
                Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-content">
                <h1 class="page-title">Dashboard</h1>
                <div class="header-actions">
                    <span class="admin-welcome">Welcome, <?php echo htmlspecialchars($admin_user['username']); ?></span>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo formatNumber($stats['total_users']); ?></h3>
                        <p class="stat-label">Total Users</p>
                        <span class="stat-change positive">+<?php echo $stats['new_users_month']; ?> this month</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo formatNumber($stats['total_courses']); ?></h3>
                        <p class="stat-label">Courses</p>
                        <span class="stat-change neutral">Active courses</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo formatNumber($stats['total_challenges']); ?></h3>
                        <p class="stat-label">CTF Challenges</p>
                        <span class="stat-change neutral">Available challenges</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo formatNumber($stats['total_solves']); ?></h3>
                        <p class="stat-label">Total Solves</p>
                        <span class="stat-change positive">Challenge completions</span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Recent Activity -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Challenge Solves</h3>
                        <a href="challenges.php" class="card-action">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if (empty($recent_solves)): ?>
                            <p class="empty-state">No recent solves</p>
                        <?php else: ?>
                            <div class="activity-list">
                                <?php foreach ($recent_solves as $solve): ?>
                                <div class="activity-item">
                                    <div class="activity-info">
                                        <span class="activity-user"><?php echo htmlspecialchars($solve['username']); ?></span>
                                        <span class="activity-action">solved</span>
                                        <span class="activity-target"><?php echo htmlspecialchars($solve['challenge_title']); ?></span>
                                    </div>
                                    <div class="activity-meta">
                                        <span class="activity-points">+<?php echo $solve['points']; ?> pts</span>
                                        <span class="activity-time"><?php echo date('M j, g:i A', strtotime($solve['solved_at'])); ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Registrations</h3>
                        <a href="users.php" class="card-action">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if (empty($recent_users)): ?>
                            <p class="empty-state">No recent registrations</p>
                        <?php else: ?>
                            <div class="user-list">
                                <?php foreach ($recent_users as $user): ?>
                                <div class="user-item">
                                    <div class="user-info">
                                        <span class="user-name"><?php echo htmlspecialchars($user['username']); ?></span>
                                        <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                    <span class="user-date"><?php echo date('M j', strtotime($user['created_at'])); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Contact Messages -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Messages</h3>
                        <a href="messages.php" class="card-action">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if (empty($recent_messages)): ?>
                            <p class="empty-state">No recent messages</p>
                        <?php else: ?>
                            <div class="message-list">
                                <?php foreach ($recent_messages as $message): ?>
                                <div class="message-item">
                                    <div class="message-info">
                                        <span class="message-from"><?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?></span>
                                        <span class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></span>
                                    </div>
                                    <div class="message-meta">
                                        <span class="message-status <?php echo $message['status']; ?>"><?php echo ucfirst($message['status']); ?></span>
                                        <span class="message-date"><?php echo date('M j', strtotime($message['created_at'])); ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-content">
                        <div class="quick-actions">
                            <a href="challenges.php?action=add" class="quick-action">
                                <span class="action-icon">➕</span>
                                <span class="action-text">Add Challenge</span>
                            </a>
                            <a href="courses.php?action=add" class="quick-action">
                                <span class="action-icon">📝</span>
                                <span class="action-text">Add Course</span>
                            </a>
                            <a href="users.php" class="quick-action">
                                <span class="action-icon">👤</span>
                                <span class="action-text">Manage Users</span>
                            </a>
                            <a href="../scoreboard.php" class="quick-action">
                                <span class="action-icon">🏅</span>
                                <span class="action-text">View Scoreboard</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
