<?php
require_once '../includes/auth_functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to submit flags.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$challenge_id = isset($_POST['challenge_id']) ? (int)$_POST['challenge_id'] : 0;
$submitted_flag = trim($_POST['flag'] ?? '');
$user = getCurrentUser();

if (!$challenge_id || !$submitted_flag || !$user) {
    echo json_encode(['success' => false, 'message' => 'Invalid challenge or flag.']);
    exit;
}

try {
    // Get challenge details
    $stmt = $pdo->prepare("SELECT * FROM ctf_challenges WHERE id = ? AND is_active = 1");
    $stmt->execute([$challenge_id]);
    $challenge = $stmt->fetch();
    
    if (!$challenge) {
        echo json_encode(['success' => false, 'message' => 'Challenge not found.']);
        exit;
    }
    
    // Check if already solved
    $stmt = $pdo->prepare("SELECT id FROM ctf_solved WHERE user_id = ? AND challenge_id = ?");
    $stmt->execute([$user['id'], $challenge_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have already solved this challenge.']);
        exit;
    }
    
    // Check flag
    $is_correct = ($submitted_flag === $challenge['flag']);
    $points_earned = $is_correct ? $challenge['points'] : 0;
    
    // Record submission
    $stmt = $pdo->prepare("INSERT INTO ctf_submissions (user_id, challenge_id, submitted_flag, is_correct, points_earned) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user['id'], $challenge_id, $submitted_flag, $is_correct, $points_earned]);
    
    if ($is_correct) {
        // Record solve
        $stmt = $pdo->prepare("INSERT INTO ctf_solved (user_id, challenge_id, points_earned) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $challenge_id, $points_earned]);
        
        // Update user total score
        $stmt = $pdo->prepare("UPDATE users SET total_score = total_score + ? WHERE id = ?");
        $stmt->execute([$points_earned, $user['id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Correct! You earned ' . $points_earned . ' points!',
            'points' => $points_earned,
            'solved' => true
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Incorrect flag. Try again!',
            'points' => 0,
            'solved' => false
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while submitting. Please try again.']);
}
?>
