<?php
require_once 'includes/auth_functions.php';

// Get challenge ID from URL
$challenge_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Get challenge details
try {
    $stmt = $pdo->prepare("
        SELECT c.*, cat.name as category_name, cat.icon as category_icon 
        FROM ctf_challenges c 
        JOIN ctf_categories cat ON c.category_id = cat.id 
        WHERE c.id = ? AND c.is_active = 1
    ");
    $stmt->execute([$challenge_id]);
    $challenge = $stmt->fetch();
    
    if (!$challenge) {
        header('Location: ctf.html');
        exit;
    }
    
    // Check if user has solved this challenge
    $is_solved = false;
    $user_submissions = [];
    
    if (isLoggedIn()) {
        $user = getCurrentUser();
        
        // Check if solved
        $stmt = $pdo->prepare("SELECT * FROM ctf_solved WHERE user_id = ? AND challenge_id = ?");
        $stmt->execute([$user['id'], $challenge_id]);
        $is_solved = (bool)$stmt->fetch();
        
        // Get user's submissions for this challenge
        $stmt = $pdo->prepare("SELECT * FROM ctf_submissions WHERE user_id = ? AND challenge_id = ? ORDER BY submitted_at DESC LIMIT 5");
        $stmt->execute([$user['id'], $challenge_id]);
        $user_submissions = $stmt->fetchAll();
    }
    
    // Get solve count
    $stmt = $pdo->prepare("SELECT COUNT(*) as solve_count FROM ctf_solved WHERE challenge_id = ?");
    $stmt->execute([$challenge_id]);
    $solve_count = $stmt->fetch()['solve_count'];
    
} catch(PDOException $e) {
    header('Location: ctf.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($challenge['title']); ?> - CTF Challenge</title>
    <meta name="description" content="<?php echo htmlspecialchars($challenge['description']); ?>">
    <link rel="stylesheet" href="assets/css/globals.css">
    <link rel="stylesheet" href="assets/css/challenge.css">
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
                <li><a href="ctf.html" class="nav-link active">CTF Challenges</a></li>
                <li><a href="blog.html" class="nav-link">Blog</a></li>
                <li><a href="about.html" class="nav-link">About</a></li>
                <li><a href="contact.html" class="nav-link">Contact</a></li>
            </ul>
            
            <div class="nav-actions">
                <a href="scoreboard.php" class="btn btn-outline">Leaderboard</a>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
                <?php else: ?>
                    <a href="login.html" class="btn btn-primary">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Challenge Header -->
    <section class="challenge-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="ctf.html">CTF Challenges</a>
                <span class="breadcrumb-separator">›</span>
                <a href="ctf.html#<?php echo $challenge['category_name']; ?>"><?php echo htmlspecialchars($challenge['category_name']); ?></a>
                <span class="breadcrumb-separator">›</span>
                <span><?php echo htmlspecialchars($challenge['title']); ?></span>
            </div>
            
            <div class="challenge-info">
                <div class="challenge-meta">
                    <span class="category-tag <?php echo strtolower($challenge['category_name']); ?>">
                        <?php echo htmlspecialchars($challenge['category_name']); ?>
                    </span>
                    <span class="difficulty-tag <?php echo $challenge['difficulty']; ?>">
                        <?php echo ucfirst($challenge['difficulty']); ?>
                    </span>
                    <span class="points-tag"><?php echo $challenge['points']; ?> points</span>
                </div>
                
                <h1 class="challenge-title"><?php echo htmlspecialchars($challenge['title']); ?></h1>
                
                <div class="challenge-stats">
                    <div class="stat">
                        <span class="stat-label">Solves</span>
                        <span class="stat-value"><?php echo $solve_count; ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Points</span>
                        <span class="stat-value"><?php echo $challenge['points']; ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Difficulty</span>
                        <span class="stat-value"><?php echo ucfirst($challenge['difficulty']); ?></span>
                    </div>
                </div>
                
                <?php if ($is_solved): ?>
                    <div class="solved-badge">
                        <img src="/placeholder.svg?height=20&width=20" alt="Solved">
                        <span>Challenge Solved!</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Challenge Content -->
    <section class="challenge-content">
        <div class="container">
            <div class="content-layout">
                <!-- Challenge Description -->
                <div class="challenge-main">
                    <div class="challenge-description card">
                        <h2 class="section-title">Challenge Description</h2>
                        <div class="description-content">
                            <?php echo nl2br(htmlspecialchars($challenge['description'])); ?>
                        </div>
                        
                        <?php if ($challenge['file_url']): ?>
                            <div class="challenge-files">
                                <h3>Challenge Files</h3>
                                <a href="<?php echo htmlspecialchars($challenge['file_url']); ?>" class="file-download btn btn-outline" download>
                                    <img src="/placeholder.svg?height=16&width=16" alt="Download">
                                    Download Files
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Flag Submission -->
                    <?php if (isLoggedIn()): ?>
                        <div class="flag-submission card">
                            <h2 class="section-title">Submit Flag</h2>
                            <?php if ($is_solved): ?>
                                <div class="solved-message">
                                    <img src="/placeholder.svg?height=24&width=24" alt="Success">
                                    <span>You have already solved this challenge!</span>
                                </div>
                            <?php else: ?>
                                <form class="flag-form" id="flagForm">
                                    <div class="form-group">
                                        <label for="flag" class="form-label">Flag</label>
                                        <div class="flag-input-container">
                                            <input 
                                                type="text" 
                                                id="flag" 
                                                name="flag" 
                                                class="form-input" 
                                                placeholder="CTF{...}" 
                                                required
                                                autocomplete="off"
                                            >
                                            <button type="submit" class="submit-btn btn btn-primary" id="submitBtn">
                                                <span class="button-text">Submit</span>
                                                <span class="button-spinner" style="display: none;">
                                                    <img src="/placeholder.svg?height=16&width=16" alt="Loading..." class="spinner">
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                            
                            <!-- Submission History -->
                            <?php if (!empty($user_submissions)): ?>
                                <div class="submission-history">
                                    <h3>Recent Submissions</h3>
                                    <div class="submissions-list">
                                        <?php foreach ($user_submissions as $submission): ?>
                                            <div class="submission-item <?php echo $submission['is_correct'] ? 'correct' : 'incorrect'; ?>">
                                                <div class="submission-flag"><?php echo htmlspecialchars($submission['submitted_flag']); ?></div>
                                                <div class="submission-result">
                                                    <?php if ($submission['is_correct']): ?>
                                                        <span class="result-correct">✓ Correct (+<?php echo $submission['points_earned']; ?> pts)</span>
                                                    <?php else: ?>
                                                        <span class="result-incorrect">✗ Incorrect</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="submission-time"><?php echo date('M j, Y H:i', strtotime($submission['submitted_at'])); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="login-prompt card">
                            <h2 class="section-title">Submit Flag</h2>
                            <p>You need to be logged in to submit flags and earn points.</p>
                            <div class="prompt-actions">
                                <a href="login.html" class="btn btn-primary">Login</a>
                                <a href="register.html" class="btn btn-outline">Sign Up</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Challenge Sidebar -->
                <div class="challenge-sidebar">
                    <!-- Hints -->
                    <?php if (!empty($challenge['hints'])): ?>
                        <div class="hints-section card">
                            <h3 class="sidebar-title">Hints</h3>
                            <div class="hints-content">
                                <div class="hint-item">
                                    <button class="hint-toggle btn btn-outline" id="hintToggle">
                                        <img src="/placeholder.svg?height=16&width=16" alt="Hint">
                                        Show Hint
                                    </button>
                                    <div class="hint-content" id="hintContent" style="display: none;">
                                        <p><?php echo nl2br(htmlspecialchars($challenge['hints'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Writeup -->
                    <?php if ($is_solved && !empty($challenge['writeup'])): ?>
                        <div class="writeup-section card">
                            <h3 class="sidebar-title">Writeup</h3>
                            <div class="writeup-content">
                                <button class="writeup-toggle btn btn-outline" id="writeupToggle">
                                    <img src="/placeholder.svg?height=16&width=16" alt="Writeup">
                                    Show Writeup
                                </button>
                                <div class="writeup-text" id="writeupContent" style="display: none;">
                                    <?php echo nl2br(htmlspecialchars($challenge['writeup'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Challenge Info -->
                    <div class="challenge-info-card card">
                        <h3 class="sidebar-title">Challenge Info</h3>
                        <div class="info-list">
                            <div class="info-item">
                                <span class="info-label">Category</span>
                                <span class="info-value"><?php echo htmlspecialchars($challenge['category_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Difficulty</span>
                                <span class="info-value"><?php echo ucfirst($challenge['difficulty']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Points</span>
                                <span class="info-value"><?php echo $challenge['points']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Solves</span>
                                <span class="info-value"><?php echo $solve_count; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Created</span>
                                <span class="info-value"><?php echo date('M j, Y', strtotime($challenge['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Related Challenges -->
                    <div class="related-challenges card">
                        <h3 class="sidebar-title">More <?php echo htmlspecialchars($challenge['category_name']); ?> Challenges</h3>
                        <div class="related-list">
                            <a href="challenge.php?id=1" class="related-item">
                                <div class="related-info">
                                    <span class="related-title">Advanced XSS</span>
                                    <span class="related-points">200 pts</span>
                                </div>
                                <span class="difficulty-tag medium">Medium</span>
                            </a>
                            <a href="challenge.php?id=2" class="related-item">
                                <div class="related-info">
                                    <span class="related-title">CSRF Protection Bypass</span>
                                    <span class="related-points">150 pts</span>
                                </div>
                                <span class="difficulty-tag hard">Hard</span>
                            </a>
                            <a href="challenge.php?id=3" class="related-item">
                                <div class="related-info">
                                    <span class="related-title">File Upload Vulnerability</span>
                                    <span class="related-points">250 pts</span>
                                </div>
                                <span class="difficulty-tag expert">Expert</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Alert Messages -->
    <div class="alert alert-success" id="successAlert" style="display: none;">
        <span class="alert-message" id="successMessage"></span>
        <button class="alert-close" id="closeSuccess">&times;</button>
    </div>

    <div class="alert alert-error" id="errorAlert" style="display: none;">
        <span class="alert-message" id="errorMessage"></span>
        <button class="alert-close" id="closeError">&times;</button>
    </div>

    <script src="assets/js/challenge.js"></script>
    <script>
        // Pass challenge data to JavaScript
        window.challengeData = {
            id: <?php echo $challenge_id; ?>,
            isSolved: <?php echo $is_solved ? 'true' : 'false'; ?>
        };
    </script>
</body>
</html>
