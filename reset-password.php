<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$message = "";
$messageType = "";

// Function to send reset password email
function sendResetEmail($email, $firstname, $token) {
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
        $mail->Subject = 'Password Reset for Nail Architect';
        
        // Create reset link (use your actual domain)
        $resetLink = 'localhost/nailarchitect/change-password.php?token=' . $token;
        
        // HTML Email Body
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e8d7d0; border-radius: 10px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="color: #ae9389; margin: 0;">Nail Architect</h1>
            </div>
            <h2 style="color: #ae9389; text-align: center;">Password Reset Request</h2>
            <p>Hello ' . $firstname . ',</p>
            <p>We received a request to reset your password. Click the button below to set a new password:</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . $resetLink . '" style="background-color: #d9bbb0; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;">Reset Password</a>
            </div>
            <p>If the button above doesn\'t work, you can also copy and paste the following link into your browser:</p>
            <p style="background-color: #f2e9e9; padding: 10px; border-radius: 5px; word-break: break-all;">' . $resetLink . '</p>
            <p>This link will expire in 1 hour for security reasons.</p>
            <p>If you didn\'t request a password reset, you can safely ignore this email.</p>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e8d7d0; text-align: center; font-size: 12px; color: #666;">
                <p>&copy; ' . date('Y') . ' Nail Architect. All rights reserved.</p>
                <p>123 Nail Street, Beauty District, Marikina City</p>
            </div>
        </div>';
        
        // Plain text alternative
        $mail->AltBody = "Hello $firstname,\n\nWe received a request to reset your password. Please reset your password by clicking this link: $resetLink\n\nThis link will expire in 1 hour.\n\nIf you didn't request this, you can ignore this email.\n\nThank you,\nNail Architect Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle form submission
if (isset($_POST['reset'])) {
    $email = $_POST['email'];
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id, first_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        $first_name = $row['first_name'];
        
        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // First, delete any existing tokens for this user
        $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $delete_stmt->bind_param("i", $user_id);
        $delete_stmt->execute();
        
        // Insert the new token
        $insert_stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iss", $user_id, $token, $expires);
        
        if ($insert_stmt->execute()) {
            // Send email with reset link
            if (sendResetEmail($email, $first_name, $token)) {
                $message = "A password reset link has been sent to your email address.";
                $messageType = "success";
            } else {
                $message = "An error occurred while sending the email. Please try again later.";
                $messageType = "error";
            }
        } else {
            $message = "An error occurred. Please try again later.";
            $messageType = "error";
        }
    } else {
        // We don't want to reveal if an email exists in the database for security reasons
        $message = "If your email address exists in our database, you will receive a password recovery link at your email address.";
        $messageType = "info";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="navbar.css">
    <link rel="icon" type="image/png" href="Assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Nail Architect - Reset Password</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #F2E9E9;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            max-width: 1200px;
            width: 100%;
            flex: 1;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
        }
        
        .logo-container img {
            height: 60px;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav-link {
            cursor: pointer;
        }
        
        .book-now {
            padding: 8px 20px;
            background-color: #e8d7d0;
            border-radius: 20px;
            cursor: pointer;
        }
        
        .reset-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            animation: fadeIn 0.6s ease-out forwards;
            padding: 20px 0;
        }
        
        .reset-form-container {
            background-color: #e8d7d0;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            animation: fadeIn 0.7s ease-out forwards;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .reset-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: #F2E9E9;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input:focus {
            outline: none;
            background-color: #ffffff;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        
        .reset-button {
            padding: 12px 24px;
            background-color: #d9bbb0;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            margin-top: 20px;
            font-weight: bold;
        }
        
        .reset-button:hover {
            background-color: #ae9389;
            transform: translateY(-2px);
        }
        
        .login-link {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
        }
        
        .login-text {
            cursor: pointer;
            font-weight: bold;
            transition: opacity 0.3s ease;
        }
        
        .login-text:hover {
            opacity: 0.7;
        }
        
        .back-button {
            display: inline-block;
            margin-top: 30px;
            font-size: 14px;
            cursor: pointer;
            position: relative;
            animation: fadeIn 0.8s ease-out forwards;
            align-self: center;
        }
        
        .back-button:after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: -2px;
            left: 0;
            background-color: #000;
            transition: width 0.3s ease;
        }
        
        .back-button:hover:after {
            width: 100%;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .reset-form-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo-container">
                <div class="logo">
                    <a href="index.php">
                        <img src="Assets/logo.png" alt="Nail Architect Logo">
                    </a>
                </div>
            </div>
            <div class="nav-links">
                <div class="nav-link">Services</div>
                <div class="book-now">Book Now</div>
                <div class="login-icon"></div>
            </div>
        </header>
        <div class="reset-container">
            <div class="reset-form-container">
                <div class="reset-title">Reset Password</div>
                
                <?php if (!empty($message)): ?>
                <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <form id="reset-form" method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <button type="submit" name="reset" class="reset-button">Send Reset Link</button>
                </form>
                
                <div class="login-link">
                    Remember your password? <span class="login-text" onclick="window.location.href='login.php'">Sign In</span>
                </div>
            </div>
            
            <div class="back-button" onclick="window.location.href='index.php'">← Back to Home</div>
        </div>
    </div>
</body>
</html>