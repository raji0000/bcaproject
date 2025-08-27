<?php
require_once 'includes/auth_functions.php';

// Get course ID from URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Get course details
try {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND is_published = 1");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        header('Location: courses.html');
        exit;
    }
    
    // Get course lessons
    $stmt = $pdo->prepare("SELECT * FROM course_lessons WHERE course_id = ? ORDER BY lesson_order ASC");
    $stmt->execute([$course_id]);
    $lessons = $stmt->fetchAll();
    
    // Check if user is enrolled (if logged in)
    $is_enrolled = false;
    $user_progress = null;
    
    if (isLoggedIn()) {
        $user = getCurrentUser();
        $stmt = $pdo->prepare("SELECT * FROM user_course_progress WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$user['id'], $course_id]);
        $user_progress = $stmt->fetch();
        $is_enrolled = (bool)$user_progress;
    }
    
} catch(PDOException $e) {
    header('Location: courses.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - CyberSec Academy</title>
    <meta name="description" content="<?php echo htmlspecialchars($course['description']); ?>">
    <link rel="stylesheet" href="assets/css/globals.css">
    <link rel="stylesheet" href="assets/css/course-detail.css">
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
                <li><a href="courses.html" class="nav-link active">Courses</a></li>
                <li><a href="ctf.html" class="nav-link">CTF Challenges</a></li>
                <li><a href="blog.html" class="nav-link">Blog</a></li>
                <li><a href="about.html" class="nav-link">About</a></li>
                <li><a href="contact.html" class="nav-link">Contact</a></li>
            </ul>
            
            <div class="nav-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                    <a href="auth/logout.php" class="btn btn-primary">Logout</a>
                <?php else: ?>
                    <a href="login.html" class="btn btn-outline">Login</a>
                    <a href="register.html" class="btn btn-primary">Join Now</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Course Hero -->
    <section class="course-hero">
        <div class="container">
            <div class="course-hero-content">
                <div class="course-info">
                    <div class="breadcrumb">
                        <a href="courses.html">Courses</a>
                        <span class="breadcrumb-separator">›</span>
                        <span><?php echo htmlspecialchars($course['title']); ?></span>
                    </div>
                    
                    <h1 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                    <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                    
                    <div class="course-meta">
                        <div class="meta-item">
                            <img src="/placeholder.svg?height=20&width=20" alt="Level">
                            <span class="course-level <?php echo $course['difficulty_level']; ?>">
                                <?php echo ucfirst($course['difficulty_level']); ?>
                            </span>
                        </div>
                        <div class="meta-item">
                            <img src="/placeholder.svg?height=20&width=20" alt="Duration">
                            <span><?php echo $course['duration_hours']; ?> hours</span>
                        </div>
                        <div class="meta-item">
                            <img src="/placeholder.svg?height=20&width=20" alt="Lessons">
                            <span><?php echo count($lessons); ?> lessons</span>
                        </div>
                        <div class="meta-item">
                            <img src="/placeholder.svg?height=20&width=20" alt="Students">
                            <span>2,500+ students</span>
                        </div>
                    </div>
                    
                    <?php if ($is_enrolled): ?>
                        <div class="progress-section">
                            <div class="progress-header">
                                <span class="progress-label">Your Progress</span>
                                <span class="progress-percentage"><?php echo number_format($user_progress['progress_percentage'], 1); ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $user_progress['progress_percentage']; ?>%"></div>
                            </div>
                        </div>
                        <div class="course-actions">
                            <a href="lesson.php?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $lessons[0]['id']; ?>" class="btn btn-primary">Continue Learning</a>
                            <button class="btn btn-outline" id="downloadCertificate" <?php echo $user_progress['progress_percentage'] < 100 ? 'disabled' : ''; ?>>
                                Download Certificate
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="course-actions">
                            <?php if (isLoggedIn()): ?>
                                <button class="btn btn-primary" id="enrollBtn" data-course-id="<?php echo $course_id; ?>">Enroll Now - Free</button>
                            <?php else: ?>
                                <a href="register.html" class="btn btn-primary">Sign Up to Enroll</a>
                                <a href="login.html" class="btn btn-outline">Already have an account?</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="course-thumbnail">
                    <img src="/placeholder.svg?height=400&width=600" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                    <?php if (!$is_enrolled): ?>
                        <div class="preview-overlay">
                            <button class="preview-btn" id="previewBtn">
                                <img src="/placeholder.svg?height=24&width=24" alt="Play">
                                Preview Course
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Course Content -->
    <section class="course-content">
        <div class="container">
            <div class="content-layout">
                <!-- Course Curriculum -->
                <div class="curriculum-section">
                    <h2 class="section-title">Course Curriculum</h2>
                    <div class="lessons-list">
                        <?php foreach ($lessons as $index => $lesson): ?>
                            <div class="lesson-item <?php echo $is_enrolled ? 'enrolled' : 'locked'; ?>">
                                <div class="lesson-header">
                                    <div class="lesson-info">
                                        <div class="lesson-number"><?php echo $index + 1; ?></div>
                                        <div class="lesson-details">
                                            <h3 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                            <div class="lesson-meta">
                                                <span class="lesson-duration"><?php echo $lesson['duration_minutes']; ?> min</span>
                                                <?php if ($lesson['video_url']): ?>
                                                    <span class="lesson-type">Video</span>
                                                <?php endif; ?>
                                                <?php if ($lesson['pdf_url']): ?>
                                                    <span class="lesson-type">PDF</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="lesson-actions">
                                        <?php if ($is_enrolled): ?>
                                            <a href="lesson.php?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $lesson['id']; ?>" class="lesson-link">
                                                <img src="/placeholder.svg?height=20&width=20" alt="Play">
                                            </a>
                                        <?php else: ?>
                                            <div class="lesson-lock">
                                                <img src="/placeholder.svg?height=20&width=20" alt="Locked">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($lesson['content']) && strlen($lesson['content']) > 0): ?>
                                    <div class="lesson-preview">
                                        <p><?php echo htmlspecialchars(substr($lesson['content'], 0, 150)) . '...'; ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Course Sidebar -->
                <div class="course-sidebar">
                    <div class="sidebar-card card">
                        <h3 class="sidebar-title">What You'll Learn</h3>
                        <ul class="learning-objectives">
                            <li>Understand fundamental cybersecurity concepts and principles</li>
                            <li>Identify common security threats and vulnerabilities</li>
                            <li>Implement basic security measures and best practices</li>
                            <li>Analyze security incidents and respond appropriately</li>
                            <li>Apply security frameworks and methodologies</li>
                        </ul>
                    </div>
                    
                    <div class="sidebar-card card">
                        <h3 class="sidebar-title">Course Features</h3>
                        <ul class="course-features">
                            <li>
                                <img src="/placeholder.svg?height=16&width=16" alt="Lifetime Access">
                                <span>Lifetime Access</span>
                            </li>
                            <li>
                                <img src="/placeholder.svg?height=16&width=16" alt="Certificate">
                                <span>Certificate of Completion</span>
                            </li>
                            <li>
                                <img src="/placeholder.svg?height=16&width=16" alt="Mobile Access">
                                <span>Mobile & Desktop Access</span>
                            </li>
                            <li>
                                <img src="/placeholder.svg?height=16&width=16" alt="Community">
                                <span>Community Support</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="sidebar-card card">
                        <h3 class="sidebar-title">Prerequisites</h3>
                        <ul class="prerequisites">
                            <li>Basic computer literacy</li>
                            <li>Understanding of internet fundamentals</li>
                            <li>No prior cybersecurity experience required</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Courses -->
    <section class="related-courses">
        <div class="container">
            <h2 class="section-title">Related Courses</h2>
            <div class="courses-grid">
                <div class="course-card card">
                    <img src="/placeholder.svg?height=150&width=250" alt="Web Application Security" class="course-image">
                    <div class="course-content">
                        <h3 class="course-title">Web Application Security</h3>
                        <div class="course-meta">
                            <span class="course-level intermediate">Intermediate</span>
                            <span class="course-duration">12 hours</span>
                        </div>
                        <a href="course-detail.php?id=2" class="btn btn-outline">View Course</a>
                    </div>
                </div>
                
                <div class="course-card card">
                    <img src="/placeholder.svg?height=150&width=250" alt="Network Security" class="course-image">
                    <div class="course-content">
                        <h3 class="course-title">Network Security Fundamentals</h3>
                        <div class="course-meta">
                            <span class="course-level intermediate">Intermediate</span>
                            <span class="course-duration">10 hours</span>
                        </div>
                        <a href="course-detail.php?id=3" class="btn btn-outline">View Course</a>
                    </div>
                </div>
                
                <div class="course-card card">
                    <img src="/placeholder.svg?height=150&width=250" alt="Cryptography Essentials" class="course-image">
                    <div class="course-content">
                        <h3 class="course-title">Cryptography Essentials</h3>
                        <div class="course-meta">
                            <span class="course-level beginner">Beginner</span>
                            <span class="course-duration">6 hours</span>
                        </div>
                        <a href="course-detail.php?id=5" class="btn btn-outline">View Course</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/course-detail.js"></script>
</body>
</html>
