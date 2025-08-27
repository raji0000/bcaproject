<?php
require_once '../includes/auth_functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to enroll in courses.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
$user = getCurrentUser();

if (!$course_id || !$user) {
    echo json_encode(['success' => false, 'message' => 'Invalid course or user.']);
    exit;
}

try {
    // Check if course exists and is published
    $stmt = $pdo->prepare("SELECT id, title FROM courses WHERE id = ? AND is_published = 1");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        echo json_encode(['success' => false, 'message' => 'Course not found.']);
        exit;
    }
    
    // Check if already enrolled
    $stmt = $pdo->prepare("SELECT id FROM user_course_progress WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user['id'], $course_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You are already enrolled in this course.']);
        exit;
    }
    
    // Get total lessons count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_lessons FROM course_lessons WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $lesson_count = $stmt->fetch()['total_lessons'];
    
    // Enroll user
    $stmt = $pdo->prepare("INSERT INTO user_course_progress (user_id, course_id, total_lessons, started_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
    $stmt->execute([$user['id'], $course_id, $lesson_count]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Successfully enrolled in ' . $course['title'] . '!',
        'redirect' => 'course-detail.php?id=' . $course_id
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while enrolling. Please try again.']);
}
?>
