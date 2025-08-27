<?php
session_start();
require_once 'config/database.php';

// Get top users by total score
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.email, u.created_at,
           COALESCE(SUM(c.points), 0) as total_score,
           COUNT(DISTINCT s.challenge_id) as challenges_solved,
           MAX(s.solved_at) as last_solve
    FROM users u
    LEFT JOIN submissions s ON u.id = s.user_id AND s.is_correct = 1
    LEFT JOIN challenges c ON s.challenge_id = c.id
    WHERE u.role = 'student'
    GROUP BY u.id, u.username, u.email, u.created_at
    ORDER BY total_score DESC, last_solve ASC
    LIMIT 50
");
$stmt->execute();
$leaderboard = $stmt->fetchAll();

// Get recent solves
$stmt = $pdo->prepare("
    SELECT u.username, c.title, c.points, s.solved_at, c.category
    FROM submissions s
    JOIN users u ON s.user_id = u.id
    JOIN challenges c ON s.challenge_id = c.id
    WHERE s.is_correct = 1
    ORDER BY s.solved_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_solves = $stmt->fetchAll();

// Get category stats
$stmt = $pdo->prepare("
    SELECT category, COUNT(*) as total_challenges,
           COUNT(DISTINCT s.user_id) as unique_solvers,
           AVG(points) as avg_points
    FROM challenges c
    LEFT JOIN submissions s ON c.id = s.challenge_id AND s.is_correct = 1
    GROUP BY category
    ORDER BY category
");
$stmt->execute();
$category_stats = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scoreboard - CyberSec Learning Platform</title>
    <link rel="stylesheet" href="assets/css/globals.css">
    <link rel="stylesheet" href="assets/css/scoreboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700&family=Open+Sans:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.html">CyberSec Platform</a>
            </div>
            <div class="nav-links">
                <a href="index.html">Home</a>
                <a href="courses.html">Courses</a>
                <a href="ctf.html">CTF</a>
                <a href="scoreboard.php" class="active">Scoreboard</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="auth/logout.php" class="btn btn-outline">Logout</a>
                <?php else: ?>
                    <a href="login.html" class="btn btn-outline">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="scoreboard-hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Leaderboard</h1>
                <p class="hero-subtitle">See how you rank against other cybersecurity enthusiasts</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="scoreboard-grid">
                <!-- Leaderboard -->
                <div class="leaderboard-section">
                    <div class="section-header">
                        <h2 class="section-title">Top Players</h2>
                        <p class="section-subtitle">Rankings based on total CTF points earned</p>
                    </div>
                    
                    <div class="leaderboard-table">
                        <div class="table-header">
                            <div class="rank-col">Rank</div>
                            <div class="user-col">User</div>
                            <div class="score-col">Score</div>
                            <div class="solves-col">Solves</div>
                            <div class="last-solve-col">Last Solve</div>
                        </div>
                        
                        <?php foreach ($leaderboard as $index => $user): ?>
                        <div class="table-row <?php echo $index < 3 ? 'top-three' : ''; ?>">
                            <div class="rank-col">
                                <span class="rank-number"><?php echo $index + 1; ?></span>
                                <?php if ($index < 3): ?>
                                    <span class="rank-medal">
                                        <?php echo $index === 0 ? '🥇' : ($index === 1 ? '🥈' : '🥉'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="user-col">
                                <div class="user-info">
                                    <span class="username"><?php echo htmlspecialchars($user['username']); ?></span>
                                    <span class="join-date">Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="score-col">
                                <span class="score"><?php echo $user['total_score']; ?></span>
                            </div>
                            <div class="solves-col">
                                <span class="solves"><?php echo $user['challenges_solved']; ?></span>
                            </div>
                            <div class="last-solve-col">
                                <span class="last-solve">
                                    <?php echo $user['last_solve'] ? date('M j, Y', strtotime($user['last_solve'])) : 'Never'; ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Recent Solves -->
                    <div class="recent-solves">
                        <h3 class="sidebar-title">Recent Solves</h3>
                        <div class="solves-list">
                            <?php foreach ($recent_solves as $solve): ?>
                            <div class="solve-item">
                                <div class="solve-header">
                                    <span class="solver"><?php echo htmlspecialchars($solve['username']); ?></span>
                                    <span class="points">+<?php echo $solve['points']; ?></span>
                                </div>
                                <div class="solve-challenge">
                                    <span class="challenge-title"><?php echo htmlspecialchars($solve['title']); ?></span>
                                    <span class="category-tag <?php echo strtolower($solve['category']); ?>">
                                        <?php echo $solve['category']; ?>
                                    </span>
                                </div>
                                <div class="solve-time">
                                    <?php echo date('M j, g:i A', strtotime($solve['solved_at'])); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Category Stats -->
                    <div class="category-stats">
                        <h3 class="sidebar-title">Category Statistics</h3>
                        <div class="stats-list">
                            <?php foreach ($category_stats as $stat): ?>
                            <div class="stat-item">
                                <div class="stat-header">
                                    <span class="category-name"><?php echo $stat['category']; ?></span>
                                    <span class="challenge-count"><?php echo $stat['total_challenges']; ?> challenges</span>
                                </div>
                                <div class="stat-details">
                                    <span class="solvers"><?php echo $stat['unique_solvers']; ?> solvers</span>
                                    <span class="avg-points">~<?php echo round($stat['avg_points']); ?> pts avg</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/scoreboard.js"></script>
</body>
</html>
