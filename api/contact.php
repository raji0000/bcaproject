<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$newsletter = isset($_POST['newsletter']) ? 1 : 0;

// Validation
$errors = [];

if (empty($first_name)) {
    $errors['first_name'] = 'First name is required';
} elseif (strlen($first_name) > 50) {
    $errors['first_name'] = 'First name must be less than 50 characters';
}

if (empty($last_name)) {
    $errors['last_name'] = 'Last name is required';
} elseif (strlen($last_name) > 50) {
    $errors['last_name'] = 'Last name must be less than 50 characters';
}

if (empty($email)) {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address';
}

if (empty($subject)) {
    $errors['subject'] = 'Subject is required';
}

if (empty($message)) {
    $errors['message'] = 'Message is required';
} elseif (strlen($message) < 10) {
    $errors['message'] = 'Message must be at least 10 characters long';
} elseif (strlen($message) > 2000) {
    $errors['message'] = 'Message must be less than 2000 characters';
}

// Return validation errors
if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    // Insert contact message into database
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (first_name, last_name, email, subject, message, newsletter_signup, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$first_name, $last_name, $email, $subject, $message, $newsletter]);
    $contact_id = $pdo->lastInsertId();

    // Send email notification (you'll need to configure your email settings)
    $email_sent = sendContactEmail($first_name, $last_name, $email, $subject, $message);

    // If newsletter signup, add to newsletter list
    if ($newsletter) {
        try {
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO newsletter_subscribers (email, name, subscribed_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$email, $first_name . ' ' . $last_name]);
        } catch (Exception $e) {
            // Newsletter signup failed, but don't fail the whole request
            error_log("Newsletter signup failed: " . $e->getMessage());
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We\'ll get back to you soon.',
        'contact_id' => $contact_id
    ]);

} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error sending your message. Please try again later.'
    ]);
}

function sendContactEmail($first_name, $last_name, $email, $subject, $message) {
    // Email configuration - you'll need to set these up
    $to = 'support@cybersecplatform.com'; // Your support email
    $email_subject = 'New Contact Form Submission: ' . $subject;
    
    $email_body = "
    New contact form submission:
    
    Name: {$first_name} {$last_name}
    Email: {$email}
    Subject: {$subject}
    
    Message:
    {$message}
    
    ---
    Sent from CyberSec Platform Contact Form
    ";
    
    $headers = [
        'From: noreply@cybersecplatform.com',
        'Reply-To: ' . $email,
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    // Use mail() function (you might want to use a more robust email service)
    return mail($to, $email_subject, $email_body, implode("\r\n", $headers));
}
?>
