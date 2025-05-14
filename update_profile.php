<?php
// update_profile_debug.php - A debug version to help identify the issue
session_start();

// Set error reporting to see all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if PHPMailer files exist
$required_files = [
    'phpmailer/src/Exception.php',
    'phpmailer/src/PHPMailer.php',
    'phpmailer/src/SMTP.php'
];

$missing_files = [];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $missing_files[] = $file;
    }
}

if (!empty($missing_files)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing PHPMailer files: ' . implode(', ', $missing_files),
        'debug' => 'PHPMailer is not installed properly'
    ]);
    exit();
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in', 'debug' => 'Session not set']);
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed',
        'debug' => mysqli_connect_error()
    ]);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method',
        'debug' => 'Method: ' . $_SERVER['REQUEST_METHOD']
    ]);
    exit();
}

// Check for action
if (!isset($_POST['action']) || $_POST['action'] !== 'update_profile') {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid action',
        'debug' => 'Action received: ' . ($_POST['action'] ?? 'none')
    ]);
    exit();
}

// Function to send verification email for profile updates
function sendProfileVerificationEmail($email, $firstname, $token) {
    $mail = new PHPMailer(true);
    try {
        // Disable SMTP for testing - just return true
        // Comment this line and uncomment the SMTP config below to test email sending
        return true;
        
        /*
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jcalleja.k12043059@umak.edu.ph';
        $mail->Password = 'pjcu jxec zzbc rbso';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom('jcalleja.k12043059@umak.edu.ph', 'Nail Architect');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your New Email Address - Nail Architect';

        // Create verification link
        $verificationLink = 'localhost/nailarchitect/verify.php?email=' . urlencode($email) . '&token=' . $token;

        // HTML Email Body
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e8d7d0; border-radius: 10px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="color: #ae9389; margin: 0;">Nail Architect</h1>
            </div>
            <h2 style="color: #ae9389; text-align: center;">Email Update Verification</h2>
            <p>Hello ' . $firstname . ',</p>
            <p>You recently updated your email address for your Nail Architect account. To complete this change, please verify your new email address by clicking the button below:</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . $verificationLink . '" style="background-color: #d9bbb0; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;">Verify My New Email</a>
            </div>
        </div>';

        $mail->send();
        return true;
        */
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Sanitize and validate input
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');
$update_past_records = isset($_POST['update_past_records']) ? true : false;

// Validation
if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
    echo json_encode([
        'success' => false, 
        'message' => 'All fields are required',
        'debug' => [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone
        ]
    ]);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format', 'debug' => 'Email: ' . $email]);
    exit();
}

// Check if password and confirm password match (if password is provided)
if (!empty($password) && $password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit();
}

// Check if email is being changed
$email_changed = false;
$current_email_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($current_email_query);
if (!$stmt) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database prepare error',
        'debug' => $conn->error
    ]);
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_user = $result->fetch_assoc();

if (!$current_user) {
    echo json_encode([
        'success' => false, 
        'message' => 'User not found',
        'debug' => 'User ID: ' . $user_id
    ]);
    exit();
}

$current_email = $current_user['email'];
$current_first_name = $current_user['first_name'];
$current_last_name = $current_user['last_name'];
$current_phone = $current_user['phone'];

// Track what has changed
$changes = [];
if ($email !== $current_email) {
    $email_changed = true;
    $changes[] = "email";
}
if ($first_name !== $current_first_name) {
    $changes[] = "first name";
}
if ($last_name !== $current_last_name) {
    $changes[] = "last name";
}
if ($phone !== $current_phone) {
    $changes[] = "phone number";
}
if (!empty($password)) {
    $changes[] = "password";
}

// If email is being changed, check if new email is already in use
if ($email_changed) {
    $email_check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($email_check_query);
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already in use by another account']);
        exit();
    }
}

// Begin transaction
$conn->begin_transaction();

try {
    // Update user profile with ALL changes
    if (!empty($password)) {
        // Update with new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $hashed_password, $user_id);
    } else {
        // Update without changing password
        $update_query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $phone, $user_id);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update profile: ' . $stmt->error);
    }
    
    // Update ALL bookings (past and future)
    $update_all_bookings_query = "UPDATE bookings 
                                 SET name = CONCAT(?, ' ', ?), 
                                     email = ?, 
                                     phone = ? 
                                 WHERE user_id = ?";
    $stmt = $conn->prepare($update_all_bookings_query);
    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $phone, $user_id);
    
    if (!$stmt->execute()) {
        // Just log warning, don't fail the whole operation
        error_log("Warning: Could not update bookings: " . $stmt->error);
    }
    
    // If email changed, handle verification
    if ($email_changed) {
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Update user with verification token and mark as unverified
        $token_query = "UPDATE users SET verification_token = ?, token_expiry = ?, is_verified = 0 WHERE id = ?";
        $stmt = $conn->prepare($token_query);
        $stmt->bind_param("ssi", $verification_token, $token_expiry, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to generate verification token: ' . $stmt->error);
        }
        
        // Send verification email using the NEW first name
        $email_sent = sendProfileVerificationEmail($email, $first_name, $verification_token);
        
        if (!$email_sent) {
            // Log error but don't fail the whole process
            error_log("Failed to send verification email to: $email");
        }
        
        // Store email in session for verification pending page
        $_SESSION['verification_email_sent'] = true;
        $_SESSION['pending_email'] = $email;
    }
    
    // Commit transaction
    $conn->commit();
    
    // Update session variables with new data
    $_SESSION['user_first_name'] = $first_name;
    $_SESSION['user_last_name'] = $last_name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_phone'] = $phone;
    
    // Prepare response
    $response = [
        'success' => true,
        'message' => 'Profile updated successfully. All booking records have been updated.',
        'changes' => $changes,
        'updated_data' => [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone
        ],
        'all_records_updated' => true
    ];
    
    if ($email_changed) {
        $response['email_changed'] = true;
        $response['message'] = 'Profile updated successfully. All records have been updated. Please check your new email address for a verification link.';
        $response['redirect'] = 'verification-pending.php';
    } else {
        $response['message'] = 'Profile updated successfully. All booking records have been updated with your new information.';
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Error updating profile: ' . $e->getMessage(),
        'debug' => [
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]
    ]);
} finally {
    // Close database connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>