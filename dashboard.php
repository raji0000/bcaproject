<?php
require_once 'includes/auth_functions.php';

// Require login to access dashboard
requireLogin();

$user = getCurrentUser();
if (!$user) {
    header('Location: login.html');
    exit;
}

// Get user statistics
try {
    // Get course progress
    $stmt = $pdo->prepare("SELECT COUNT(*) as enrolled_courses FROM user_course_progress WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $course_stats = $stmt->fetch();
    
    // Get completed courses
    $stmt = $pdo->prepare("SELECT COUNT(*) as completed_courses FROM user_course_progress WHERE user_id = ? AND progress_percentage = 100");
    $stmt->execute([$user['id']]);
    $completed_stats = $stmt->fetch();
    
    // Get CTF challenges solved
    $stmt = $pdo->prepare("SELECT COUNT(*) as solved_challenges FROM ctf_solved WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $ctf_stats = $stmt->fetch();
    
    // Get recent activity
    $stmt = $pdo->prepare("
        SELECT 'course' as type, c.title, ucp.started_at as date 
        FROM user_course_progress ucp 
        JOIN courses c ON ucp.course_id = c.id 
        WHERE ucp.user_id = ? 
        UNION ALL
        SELECT 'ctf' as type, ch.title, cs.solved_at as date 
        FROM ctf_solved cs 
        JOIN ctf_challenges ch ON cs.challenge_id = ch.id 
        WHERE cs.user_id = ? 
        ORDER BY date DESC 
        LIMIT 5
    ");
    $stmt->execute([$user['id'], $user['id']]);
    $recent_activity = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $course_stats = ['enrolled_courses' => 0];
    $completed_stats = ['completed_courses' => 0];
    $ctf_stats = ['solved_challenges' => 0];
    $recent_activity = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CyberSec Academy</title>
    <meta name="description" content="Your personal dashboard for tracking cybersecurity learning progress.">
    <link rel="stylesheet" href="assets/css/globals.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="icon" href="assets/images/favicon.ico">
</head>
<body>
    <!-- Header Navigation -->
    <header class="header">
        <nav class="nav container">
            <div class="nav-brand">
                <a href="index.html" class="logo">
                    <img src="/placeholder.svg?height=40&width=40" alt="CyberSec Academy" class="logo-img">
                    <span class="logo-text">CyberSec Academy</span>
                </a>
            </div>
            
            <ul class="nav-links">
                <li><a href="courses.html" class="nav-link">Courses</a></li>
                <li><a href="ctf.html" class="nav-link">CTF Challenges</a></li>
                <li><a href="blog.html" class="nav-link">Blog</a></li>
                <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
            </ul>
            
            <div class="nav-actions">
                <div class="user-menu">
                    <button class="user-menu-toggle" id="userMenuToggle">
                        <img src="/placeholder.svg?height=32&width=32" alt="<?php echo htmlspecialchars($user['first_name']); ?>" class="user-avatar">
                        <span class="user-name"><?php echo htmlspecialchars($user['first_name']); ?></span>
                        <span class="dropdown-arrow">▼</span>
                    </button>
                    <div class="user-menu-dropdown" id="userMenuDropdown">
                        <a href="profile.php" class="dropdown-item">Profile</a>
                        <a href="settings.php" class="dropdown-item">Settings</a>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="admin/index.php" class="dropdown-item">Admin Panel</a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="auth/logout.php" class="dropdown-item">Logout</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Dashboard Content -->
    <main class="dashboard-main">
        <div class="container">
            <!-- Welcome Section -->
            <section class="welcome-section">
                <div class="welcome-content">
                    <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                    <p class="welcome-subtitle">Continue your cybersecurity journey and track your progress</p>
                </div>
                <div class="welcome-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $user['total_score']; ?></div>
                        <div class="stat-label">Total Points</div>
                    </div>
                </div>
            </section>

            <!-- Stats Overview -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card card">
                        <div class="stat-icon">
                            <img src="/placeholder.svg?height=32&width=32" alt="Courses">
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $course_stats['enrolled_courses']; ?></div>
                            <div class="stat-label">Enrolled Courses</div>
                        </div>
                    </div>
                    
                    <div class="stat-card card">
                        <div class="stat-icon">
                            <img src="/placeholder.svg?height=32&width=32" alt="Completed">
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $completed_stats['completed_courses']; ?></div>
                            <div class="stat-label">Completed Courses</div>
                        </div>
                    </div>
                    
                    <div class="stat-card card">
                        <div class="stat-icon">
                            <img src="/placeholder.svg?height=32&width=32" alt="CTF">
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $ctf_stats['solved_challenges']; ?></div>
                            <div class="stat-label">CTF Challenges Solved</div>
                        </div>
                    </div>
                    
                    <div class="stat-card card">
                        <div class="stat-icon">
                            <img src="/placeholder.svg?height=32&width=32" alt="Rank">
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">#<?php echo rand(1, 100); ?></div>
                            <div class="stat-label">Global Rank</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="quick-actions-section">
                <h2 class="section-title">Quick Actions</h2>
                <div class="quick-actions-grid">
                    <a href="courses.html" class="action-card card">
                        <div class="action-icon">
                            <img src="/placeholder.svg?height=48&width=48" alt="Browse Courses">
                        </div>
                        <h3 class="action-title">Browse Courses</h3>
                        <p class="action-description">Explore our comprehensive cybersecurity courses</p>
                    </a>
                    
                    <a href="ctf.html" class="action-card card">
                        <div class="action-icon">
                            <img src="/placeholder.svg?height=48&width=48" alt="CTF Challenges">
                        </div>
                        <h3 class="action-title">CTF Challenges</h3>
                        <p class="action-description">Test your skills with hands-on challenges</p>
                    </a>
                    
                    <a href="profile.php" class="action-card card">
                        <div class="action-icon">
                            <img src="/placeholder.svg?height=48&width=48" alt="Update Profile">
                        </div>
                        <h3 class="action-title">Update Profile</h3>
                        <p class="action-description">Manage your account settings and preferences</p>
                    </a>
                </div>
            </section>

            <!-- Recent Activity -->
            <section class="activity-section">
                <h2 class="section-title">Recent Activity</h2>
                <div class="activity-list card">
                    <?php if (empty($recent_activity)): ?>
                        <div class="empty-state">
                            <img src="/placeholder.svg?height=64&width=64" alt="No activity" class="empty-icon">
                            <h3 class="empty-title">No recent activity</h3>
                            <p class="empty-description">Start learning to see your progress here!</p>
                            <a href="courses.html" class="btn btn-primary">Browse Courses</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <img src="/placeholder.svg?height=24&width=24" alt="<?php echo $activity['type']; ?>">
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?php if ($activity['type'] === 'course'): ?>
                                            Started course: <?php echo htmlspecialchars($activity['title']); ?>
                                        <?php else: ?>
                                            Solved challenge: <?php echo htmlspecialchars($activity['title']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-date"><?php echo date('M j, Y', strtotime($activity['date'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <script src="assets/js/dashboard.js"></script>
</body>
</html>
