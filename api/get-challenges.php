<?php
require_once '../includes/auth_functions.php';

header('Content-Type: application/json');

$category = isset($_GET['category']) ? $_GET['category'] : '';
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : 'all';

if (empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Category is required.']);
    exit;
}

try {
    // Build query
    $query = "
        SELECT c.*, cat.name as category_name,
        (SELECT COUNT(*) FROM ctf_solved WHERE challenge_id = c.id) as solve_count
        FROM ctf_challenges c 
        JOIN ctf_categories cat ON c.category_id = cat.id 
        WHERE cat.name = ? AND c.is_active = 1
    ";
    
    $params = [$category];
    
    if ($difficulty !== 'all') {
        $query .= " AND c.difficulty = ?";
        $params[] = $difficulty;
    }
    
    $query .= " ORDER BY c.points ASC, c.created_at ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $challenges = $stmt->fetchAll();
    
    // If user is logged in, check which challenges they've solved
    $solved_challenges = [];
    if (isLoggedIn()) {
        $user = getCurrentUser();
        $stmt = $pdo->prepare("SELECT challenge_id FROM ctf_solved WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $solved_challenges = array_column($stmt->fetchAll(), 'challenge_id');
    }
    
    // Add solved status to challenges
    foreach ($challenges as &$challenge) {
        $challenge['is_solved'] = in_array($challenge['id'], $solved_challenges);
    }
    
    echo json_encode([
        'success' => true,
        'challenges' => $challenges
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching challenges.']);
}
?>
