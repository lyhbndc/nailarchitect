<?php
session_start();

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Function to send verification email for profile updates
function sendProfileVerificationEmail($email, $firstname, $token) {
    $mail = new PHPMailer(true);
    try {
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
            <p>If the button above doesn\'t work, you can also copy and paste the following link into your browser:</p>
            <p style="background-color: #f2e9e9; padding: 10px; border-radius: 5px; word-break: break-all;">' . $verificationLink . '</p>
            <p>This link will expire in 24 hours for security reasons.</p>
            <p>If you didn\'t request this email change, please contact our support team immediately.</p>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e8d7d0; text-align: center; font-size: 12px; color: #666;">
                <p>&copy; ' . date('Y') . ' Nail Architect. All rights reserved.</p>
                <p>123 Nail Street, Beauty District, Marikina City</p>
            </div>
        </div>';

        // Plain text alternative
        $mail->AltBody = "Hello $firstname,\n\nPlease verify your new email address by clicking this link: $verificationLink\n\nThank you,\nNail Architect Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check for action
if (!isset($_POST['action']) || $_POST['action'] !== 'update_profile') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
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
$update_past_records = isset($_POST['update_past_records']) ? true : false; // Optional checkbox

// Validation
if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
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
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_user = $result->fetch_assoc();
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
    // First, create a backup of old data in a history table (optional but recommended)
    $backup_query = "INSERT INTO user_profile_history (user_id, old_first_name, old_last_name, old_email, old_phone, new_first_name, new_last_name, new_email, new_phone, changed_at, update_past_records) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($backup_query);
    $update_past_int = $update_past_records ? 1 : 0;
    $stmt->bind_param("issssssssi", $user_id, $current_first_name, $current_last_name, $current_email, $current_phone, $first_name, $last_name, $email, $phone, $update_past_int);
    // Execute backup (optional - uncomment if you have this table)
    // $stmt->execute();
    
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
        throw new Exception('Failed to update profile');
    }
    
    // VERSION 1: Update ALL bookings (past and future)
    // This will update every single booking regardless of status or date
    $update_all_bookings_query = "UPDATE bookings 
                                 SET name = CONCAT(?, ' ', ?), 
                                     email = ?, 
                                     phone = ? 
                                 WHERE user_id = ?";
    $stmt = $conn->prepare($update_all_bookings_query);
    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $phone, $user_id);
    
    if (!$stmt->execute()) {
        error_log("Warning: Could not update bookings, but profile update succeeded");
    }
    
    // Update messages table if it has sender name
    $update_messages_query = "UPDATE messages 
                             SET sender_name = CONCAT(?, ' ', ?) 
                             WHERE sender_id = ?";
    $stmt = $conn->prepare($update_messages_query);
    $stmt->bind_param("ssi", $first_name, $last_name, $user_id);
    // Execute if you have such a column
    // $stmt->execute();
    
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
            throw new Exception('Failed to generate verification token');
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
    echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()]);
} finally {
    // Close database connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>