<?php
// Start session
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Function to generate verification token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Function to send verification email
function sendVerificationEmail($userEmail, $userName, $verificationToken) {
    // Create new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jcalleja.k12043059@umak.edu.ph';
        $mail->Password = 'pjcu jxec zzbc rbso';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        
        // Sender and recipient
        $mail->setFrom('jcalleja.k12043059@umak.edu.ph', 'Nail Architect');
        $mail->addAddress($userEmail);
        
        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Nail Architect Account';
        
        // Generate verification URL - MATCH THE URL FORMAT FROM sign-up.php
        $verifyUrl = 'localhost/nailarchitect/verify.php?email=' . urlencode($userEmail) . '&token=' . $verificationToken;
        
        // Email body
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e8d7d0; border-radius: 10px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="color: #ae9389; margin: 0;">Nail Architect</h1>
            </div>
            <h2 style="color: #ae9389; text-align: center;">Verify Your Email</h2>
            <p>Hello ' . $userName . ',</p>
            <p>Thank you for creating an account with Nail Architect. To complete your registration and access all our services, please verify your email address by clicking the button below:</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . $verifyUrl . '" style="background-color: #d9bbb0; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;">Verify My Email</a>
            </div>
            <p>If the button above doesn\'t work, you can also copy and paste the following link into your browser:</p>
            <p style="background-color: #f2e9e9; padding: 10px; border-radius: 5px; word-break: break-all;">' . $verifyUrl . '</p>
            <p>This link will expire in 24 hours for security reasons.</p>
            <p>If you didn\'t create an account with us, you can safely ignore this email.</p>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e8d7d0; text-align: center; font-size: 12px; color: #666;">
                <p>&copy; ' . date('Y') . ' Nail Architect. All rights reserved.</p>
                <p>46 Osmena St., TS Cruz Subdivision Novaliches, Quezon City</p>
            </div>
        </div>';
        
        // Plain text alternative
        $mail->AltBody = "Hello $userName,\n\nPlease verify your email by clicking this link: $verifyUrl\n\nThank you,\nNail Architect Team";
        
        // Send email
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Return false if email fails to send
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Initialize variables
$message = '';
$status = '';

// Process form submission
if (isset($_POST['resend'])) {
    // Get email from form
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if email exists
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        // User exists, fetch details
        $user = mysqli_fetch_assoc($result);
        
        // Check if already verified
        if ($user['is_verified'] == 1) {
            $message = "This email is already verified. You can log in to your account.";
            $status = "info";
        } else {
            // Generate new verification token
            $token = generateToken();
            $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Update verification token in database
            $update_query = "UPDATE users SET verification_token = '$token', token_expiry = '$expiry' WHERE email = '$email'";
            $update_result = mysqli_query($conn, $update_query);
            
            if ($update_result) {
                // Send verification email
                $emailSent = sendVerificationEmail($email, $user['first_name'], $token);
                
                if ($emailSent) {
                    $message = "A new verification email has been sent to your email address. Please check your inbox and spam folder.";
                    $status = "success";
                    
                    // Set session for verification pending
                    $_SESSION['verification_email_sent'] = true;
                } else {
                    $message = "We couldn't send the verification email. Please try again later.";
                    $status = "error";
                }
            } else {
                $message = "Something went wrong. Please try again later.";
                $status = "error";
            }
        }
    } else {
        // Email not found
        $message = "We couldn't find an account with that email address. Please check your email or sign up for a new account.";
        $status = "error";
    }
}

// Clear any database connections
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification - Nail Architect</title>
    <link rel="icon" type="image/png" href="Assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #F2E9E9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            flex: 1;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .logo img {
            height: 60px;
        }
        
        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        
        .card {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .card-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            font-size: 14px;
            margin-bottom: 8px;
            color: #555;
        }
        
        input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus {
            border-color: #ae9389;
            outline: none;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #d9bbb0;
            color: #333;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
        }
        
        .btn:hover {
            background-color: #c4a99e;
            transform: translateY(-2px);
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-4 {
            margin-top: 20px;
        }
        
        .text-muted {
            color: #6c757d;
            font-size: 14px;
        }
        
        a {
            color: #ae9389;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        a:hover {
            color: #8c7267;
        }
        
        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        footer {
            background-color: #333;
            color: #fff;
            padding: 30px 0;
            margin-top: auto;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 0 20px;
        }
        
        .footer-section {
            flex: 1;
            min-width: 200px;
            margin-bottom: 20px;
        }
        
        .footer-section h3 {
            margin-bottom: 15px;
            font-size: 18px;
            color: #e8d7d0;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 8px;
        }
        
        .footer-section ul li a {
            color: #ddd;
            text-decoration: none;
        }
        
        .footer-section ul li a:hover {
            color: #fff;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #444;
            margin-top: 20px;
            font-size: 14px;
            color: #aaa;
        }
        
        @media (max-width: 768px) {
            .card {
                padding: 30px 20px;
                margin: 0 15px 30px;
            }
            
            .footer-section {
                flex: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <a href="index.php">
                    <img src="Assets/logo.png" alt="Nail Architect Logo">
                </a>
            </div>
        </header>
        
        <div class="main-content">
            <div class="card">
                <h2 class="card-title">Resend Verification Email</h2>
                
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $status; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($status != 'success' && $status != 'info'): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                    </div>
                    
                    <button type="submit" name="resend" class="btn">Send Verification Email</button>
                </form>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <?php if ($status == 'success'): ?>
                    <p class="text-muted">After verifying your email, you can <a href="login.php">log in to your account</a>.</p>
                    <p class="text-muted mt-4"><a href="verification-pending.php">Go to verification pending page</a></p>
                    <?php elseif ($status == 'info'): ?>
                    <p class="text-muted">You can now <a href="login.php">log in to your account</a>.</p>
                    <?php else: ?>
                    <p class="text-muted">Remember to check your spam/junk folder if you don't see the email in your inbox.</p>
                    <div class="mt-4">
                        <p class="text-muted">Don't have an account? <a href="sign-up.php">Sign up here</a></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>
    
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Nail Architect</h3>
                <p>Your destination for premium nail care and beauty services.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="#">Booking</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>123 Nail Street, Beauty District<br>
                Phone: (123) 456-7890<br>
                Email: info@nailarchitect.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Nail Architect. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>