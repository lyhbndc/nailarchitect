<?php
// Start session
session_start();

// Check if user came from signup page
if (!isset($_SESSION['verification_email_sent'])) {
    header("Location: signup.php");
    exit();
}

// Clear the session variable
unset($_SESSION['verification_email_sent']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="navbar.css">
    <link rel="icon" type="image/png" href="Assets/favicon.png">
    <title>Verification Pending - Nail Architect</title>
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
        
        .pending-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 50px 0;
            text-align: center;
        }
        
        .pending-box {
            background-color: #e8d7d0;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .envelope-icon {
            font-size: 70px;
            margin-bottom: 20px;
            color: #ae9389;
        }
        
        .pending-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .pending-message {
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .steps-container {
            text-align: left;
            margin: 20px 0;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 10px;
        }
        
        .step {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
        }
        
        .step-number {
            background-color: #d9bbb0;
            color: #000;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
            flex-shrink: 0;
        }
        
        .resend-link {
            margin-top: 20px;
            color: #ae9389;
            text-decoration: underline;
            cursor: pointer;
        }
        
        .home-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #d9bbb0;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            color: black;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .home-button:hover {
            background-color: #ae9389;
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
        
        <div class="pending-container">
            <div class="pending-box">
                <div class="envelope-icon">
                    ✉️
                </div>
                
                <div class="pending-title">
                    Check Your Email
                </div>
                
                <div class="pending-message">
                    <p>Thank you for signing up! We've sent a verification link to your email address.</p>
                    <p>Please check your inbox and click the verification link to activate your account.</p>
                </div>
                
                <div class="steps-container">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div>Check your email inbox (and spam/junk folder just in case)</div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div>Open the email from "Nail Architect"</div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div>Click the "Verify My Email" button in the email</div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div>Once verified, you can log in to your account</div>
                    </div>
                </div>
                
                <div class="resend-link">
                    <a href="resend-verification.php">Didn't receive an email? Resend verification link</a>
                </div>
            </div>
            
            <a href="index.php" class="home-button">Return to Home</a>
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
            <p>&copy; 2025 Nail Architect. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>