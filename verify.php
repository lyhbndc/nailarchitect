<?php
// Start session
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = "";
$status = "error";

// Check if both email and token are provided in the URL
if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = mysqli_real_escape_string($conn, $_GET['email']);
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    // Check if the email and token combination exists and is valid
    $query = "SELECT * FROM users WHERE email = '$email' AND verification_token = '$token' AND token_expiry > NOW() AND is_verified = 0";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        // Update user as verified
        $update_query = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE email = '$email'";
        $update_result = mysqli_query($conn, $update_query);
        
        if ($update_result) {
            $message = "Your email has been successfully verified! You can now log in to your account.";
            $status = "success";
            
            // Get user details for a personalized message
            $user = mysqli_fetch_assoc($result);
            $firstname = $user['first_name'];
        } else {
            $message = "Error verifying your email. Please try again or contact support.";
        }
    } else {
        // Check if the user is already verified
        $check_query = "SELECT * FROM users WHERE email = '$email' AND is_verified = 1";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "Your email is already verified. Please log in to your account.";
            $status = "info";
        } else {
            $message = "Invalid or expired verification link. Please request a new verification email.";
        }
    }
} else {
    $message = "Invalid verification link. Please check your email and try again.";
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
    <title>Email Verification - Nail Architect</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins, sans-serif;
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
            max-width: 1500px;
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
        
        
        .verification-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 50px 0;
            text-align: center;
        }
        
        .verification-box {
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .verification-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .success-icon {
            color: #4caf50;
        }
        
        .error-icon {
            color: #f44336;
        }
        
        .info-icon {
            color: #2196f3;
        }
        
        .verification-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .verification-message {
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .verification-button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            color: black;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .verification-button:hover {
            background: linear-gradient(to right, #d98d8d, #ce7878);
            transform: translateY(-2px);
        }
        
        /* Footer styles */
        footer {
            background-color: #333;
            color: white;
            padding: 20px 0;
            margin-top: 40px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        
        .footer-section {
            flex: 1;
            min-width: 200px;
            margin-bottom: 20px;
            padding: 0 15px;
        }
        
        .footer-section h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .footer-section ul {
            list-style: none;
            padding: 0;
        }
        
        .footer-section ul li {
            margin-bottom: 8px;
        }
        
        .footer-section a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-section a:hover {
            color: white;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #444;
            margin-top: 20px;
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
        </header>
        
        <div class="verification-container">
            <div class="verification-box">
                <div class="verification-icon <?php echo $status; ?>-icon">
                    <?php 
                    if ($status == 'success') {
                        echo '✓';
                    } elseif ($status == 'error') {
                        echo '✗';
                    } else {
                        echo 'ℹ';
                    }
                    ?>
                </div>
                
                <div class="verification-title">
                    <?php 
                    if ($status == 'success') {
                        echo 'Email Verified!';
                    } elseif ($status == 'error') {
                        echo 'Verification Failed';
                    } else {
                        echo 'Email Verification';
                    }
                    ?>
                </div>
                
                <div class="verification-message">
                    <?php echo $message; ?>
                    
                    <?php if ($status == 'success' && isset($firstname)): ?>
                    <p>Welcome to Nail Architect, <?php echo htmlspecialchars($firstname); ?>! We're excited to have you join us.</p>
                    <?php endif; ?>
                </div>
                
                <?php if ($status == 'success' || $status == 'info'): ?>
                <a href="login.php" class="verification-button">Log In Now</a>
                <?php elseif ($status == 'error'): ?>
                <a href="resend-verification.php" class="verification-button">Resend Verification</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</body>
</html>