<?php
session_start();
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$valid_token = false;
$token = "";
$user_email = "";
$debug_info = "";

// Check if token is provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $debug_info .= "Token from URL: " . $token . "<br>";
    
    // Debugging: Show all tokens in the database
    $debug_query = "SELECT id, user_id, token, expires_at FROM password_resets";
    $debug_result = mysqli_query($conn, $debug_query);
    
    $debug_info .= "<h3>Tokens in Database:</h3>";
    $debug_info .= "<table border='1' style='width: 100%; border-collapse: collapse;'>";
    $debug_info .= "<tr><th>ID</th><th>User ID</th><th>Token</th><th>Expires At</th><th>Status</th></tr>";
    
    while ($row = mysqli_fetch_assoc($debug_result)) {
        $now = new DateTime();
        $expires = new DateTime($row['expires_at']);
        $is_expired = $expires < $now ? "Expired" : "Valid";
        $is_match = $row['token'] === $token ? "MATCH" : "";
        
        $debug_info .= "<tr>";
        $debug_info .= "<td>" . $row['id'] . "</td>";
        $debug_info .= "<td>" . $row['user_id'] . "</td>";
        $debug_info .= "<td>" . substr($row['token'], 0, 10) . "...</td>";
        $debug_info .= "<td>" . $row['expires_at'] . "</td>";
        $debug_info .= "<td>" . $is_expired . " " . $is_match . "</td>";
        $debug_info .= "</tr>";
    }
    $debug_info .= "</table>";
    
    $debug_info .= "<p>Current server time: " . date('Y-m-d H:i:s') . "</p>";
    
    // Verify token exists in database first, without expiration check
    $stmt = $conn->prepare("SELECT pr.id, pr.user_id, pr.expires_at, u.email, u.first_name 
                           FROM password_resets pr 
                           JOIN users u ON pr.user_id = u.id 
                           WHERE pr.token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // Separate check for expiration
        $now = time();
        $expires = strtotime($row['expires_at']);
        
        if ($expires > $now) {
            $valid_token = true;
            $user_id = $row['user_id'];
            $user_email = $row['email'];
            $first_name = $row['first_name'];
            $debug_info .= "<p style='color:green'>Token is valid and not expired!</p>";
        } else {
            $message = "Token has expired. Please request a new password reset link.";
            $messageType = "error";
            $debug_info .= "<p style='color:red'>Token found but has expired.</p>";
            $debug_info .= "<p>Token expires at: " . $row['expires_at'] . "</p>";
        }
    } else {
        $message = "Invalid token. Please request a new password reset link.";
        $messageType = "error";
        $debug_info .= "<p style='color:red'>No matching token found in database.</p>";
    }
}

// Function to send password change confirmation email
function sendPasswordChangedEmail($email, $firstname) {
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
        $mail->Subject = 'Your Nail Architect Password Has Been Changed';
        
        // HTML Email Body
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e8d7d0; border-radius: 10px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="color: #ae9389; margin: 0;">Nail Architect</h1>
            </div>
            <h2 style="color: #ae9389; text-align: center;">Password Changed Successfully</h2>
            <p>Hello ' . $firstname . ',</p>
            <p>Your password for Nail Architect has been successfully changed.</p>
            <p>If you did not make this change, please contact us immediately by replying to this email.</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="http://localhost/nailarchitect/login.php" style="background-color: #d9bbb0; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;">Log In Now</a>
            </div>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e8d7d0; text-align: center; font-size: 12px; color: #666;">
                <p>&copy; ' . date('Y') . ' Nail Architect. All rights reserved.</p>
                <p>123 Nail Street, Beauty District, Marikina City</p>
            </div>
        </div>';
        
        $mail->AltBody = "Hello $firstname,\n\nYour password for Nail Architect has been successfully changed.\n\nIf you did not make this change, please contact us immediately.\n\nThank you,\nNail Architect Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle form submission
if (isset($_POST['change_password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate token - without expiration check in SQL
    $stmt = $conn->prepare("SELECT pr.id, pr.user_id, pr.expires_at, u.email, u.first_name 
                           FROM password_resets pr 
                           JOIN users u ON pr.user_id = u.id 
                           WHERE pr.token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // Check expiration separately
        $now = time();
        $expires = strtotime($row['expires_at']);
        
        if ($expires > $now) {
            $reset_id = $row['id'];
            $user_id = $row['user_id'];
            $user_email = $row['email'];
            $first_name = $row['first_name'];
            
            // Check if passwords match
            if ($password !== $confirm_password) {
                $message = "Passwords do not match. Please try again.";
                $messageType = "error";
                $valid_token = true; // Keep the form accessible
            } else {
                // Password validation (same as sign-up page)
                $passwordRegex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/";
                if (!preg_match($passwordRegex, $password)) {
                    $message = "Password must be at least 8 characters with uppercase, lowercase, and number.";
                    $messageType = "error";
                    $valid_token = true; // Keep the form accessible
                } else {
                    // Hash the new password
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    
                    // Update the user's password
                    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($update_stmt->execute()) {
                        // Delete the password reset token
                        $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE id = ?");
                        $delete_stmt->bind_param("i", $reset_id);
                        $delete_stmt->execute();
                        
                        // Send confirmation email
                        sendPasswordChangedEmail($user_email, $first_name);
                        
                        $message = "Your password has been updated successfully. You can now <a href='login.php'>login</a> with your new password.";
                        $messageType = "success";
                        $valid_token = false; // Hide the form
                    } else {
                        $message = "An error occurred while updating your password. Please try again.";
                        $messageType = "error";
                        $valid_token = true; // Keep the form accessible
                    }
                }
            }
        } else {
            $message = "Token has expired. Please request a new password reset link.";
            $messageType = "error";
        }
    } else {
        $message = "Invalid or expired token. Please request a new password reset link.";
        $messageType = "error";
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
    <title>Nail Architect - Change Password</title>
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
        
        .change-password-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            animation: fadeIn 0.6s ease-out forwards;
            padding: 20px 0;
        }
        
        .change-password-form-container {
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            animation: fadeIn 0.7s ease-out forwards;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .debug-container {
            background-color: #fff;
            border-radius: 15px;
            padding: 20px;
            width: 100%;
            max-width: 800px;
            margin-top: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        
        .change-password-title {
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
            font-family: Poppins;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input:focus {
            outline: none;
            background-color: #ffffff;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        
        .password-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            cursor: pointer;
            color: #666;
            font-size: 16px;
            user-select: none;
            transition: color 0.3s ease;
        }
        
        .toggle-password:hover {
            color: #333;
        }
        
        .change-button {
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
        
        .change-button:hover {
            background-color: #ae9389;
            transform: translateY(-2px);
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
        
        /* Password strength meter */
        .password-strength-meter {
            height: 4px;
            width: 100%;
            background-color: #e0e0e0;
            margin-top: 8px;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-meter-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .strength-weak .password-strength-meter-bar {
            width: 33.33%;
            background-color: #f44336; /* Red */
        }
        
        .strength-medium .password-strength-meter-bar {
            width: 66.66%;
            background-color: #ff9800; /* Orange */
        }
        
        .strength-strong .password-strength-meter-bar {
            width: 100%;
            background-color: #4caf50; /* Green */
        }
        
        .strength-text {
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .change-password-form-container {
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
        <div class="change-password-container">
            <div class="change-password-form-container">
                <div class="change-password-title">Change Password</div>
                
                <?php if (!empty($message)): ?>
                <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if ($valid_token): ?>
                <form id="change-password-form" method="POST" action="">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="password-input-container">
                            <input type="password" id="password" name="password" required minlength="8" oninput="checkPasswordStrength()">
                            <i id="toggle-password-icon" class="fa fa-eye toggle-password" onclick="togglePasswordVisibility('password', 'toggle-password-icon')"></i>
                        </div>
                        <div id="password-strength-meter" class="password-strength-meter" style="display: none;">
                            <div id="password-strength-meter-bar" class="password-strength-meter-bar"></div>
                        </div>
                        <span id="password-strength" class="strength-text"></span>
                        <div class="password-requirements">
                            Password must be at least 8 characters with uppercase, lowercase, and number
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm-password">Confirm New Password</label>
                        <div class="password-input-container">
                            <input type="password" id="confirm-password" name="confirm_password" required minlength="8" oninput="checkPasswordMatch()">
                            <i id="toggle-confirm-password-icon" class="fa fa-eye toggle-password" onclick="togglePasswordVisibility('confirm-password', 'toggle-confirm-password-icon')"></i>
                        </div>
                        <span id="match-status" class="match-status"></span>
                    </div>
                    
                    <button type="submit" name="change_password" class="change-button" id="change-button">Change Password</button>
                </form>
                <?php elseif (empty($message)): ?>
                <div class="message error">Invalid or expired password reset link. Please request a new one.</div>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="reset-password.php" style="color: inherit; text-decoration: none; font-weight: bold;">Request New Password Reset</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Debug information container
            <div class="debug-container">
                <h2>Debug Information</h2>
                
            </div> -->
            
            <!-- <div class="back-button" onclick="window.location.href='login.php'">← Back to Login</div> -->
        </div>
    </div>
    
    <script>
    function togglePasswordVisibility(fieldId, iconId) {
        const passwordField = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(iconId);
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
    
    function checkPasswordStrength() {
        const password = document.getElementById('password').value;
        const strengthIndicator = document.getElementById('password-strength');
        const meterBar = document.getElementById('password-strength-meter-bar');
        const meterContainer = document.getElementById('password-strength-meter');
        
        // Remove all strength classes
        meterContainer.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
        
        if (!password) {
            strengthIndicator.textContent = '';
            meterContainer.style.display = 'none'; // Hide the meter when empty
            return;
        }
        
        // Show the meter when user is typing
        meterContainer.style.display = 'block';
        
        let strength = 'Weak';
        let strengthClass = 'strength-weak';
        let textColor = '#f44336'; // Red color for weak
        
        // Basic checks
        const hasMinLength = password.length >= 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChars = /[^A-Za-z0-9]/.test(password);
        
        // Determine strength
        if (hasMinLength && hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChars) {
            strength = 'Strong';
            strengthClass = 'strength-strong';
            textColor = '#4caf50'; // Green color for strong
        } else if (hasMinLength && 
                  ((hasUpperCase && hasLowerCase && hasNumbers) || 
                   (hasUpperCase && hasLowerCase && hasSpecialChars) ||
                   (hasLowerCase && hasNumbers && hasSpecialChars))) {
            strength = 'Medium';
            strengthClass = 'strength-medium';
            textColor = '#ff9800'; // Orange color for medium
        }
        
        // Update UI
        strengthIndicator.textContent = `Password strength: ${strength}`;
        strengthIndicator.style.color = textColor; // Set text color
        meterContainer.classList.add(strengthClass);
    }
    
    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const matchStatus = document.getElementById('match-status');
        const changeButton = document.getElementById('change-button');

        if (!confirmPassword) {
            matchStatus.textContent = '';
            return;
        }

        if (password === confirmPassword) {
            matchStatus.textContent = 'Passwords match';
            matchStatus.style.color = 'green';
            changeButton.disabled = false;
        } else {
            matchStatus.textContent = 'Passwords do not match';
            matchStatus.style.color = 'red';
            changeButton.disabled = true;
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Disable submit button initially if both fields are empty
        const passwordField = document.getElementById('password');
        const confirmField = document.getElementById('confirm-password');
        const changeButton = document.getElementById('change-button');
        
        if (passwordField && confirmField) {
            changeButton.disabled = true;
            
            // Check on input change
            passwordField.addEventListener('input', validateForm);
            confirmField.addEventListener('input', validateForm);
            
            function validateForm() {
                if (passwordField.value.length >= 8 && confirmField.value.length >= 8 && passwordField.value === confirmField.value) {
                    changeButton.disabled = false;
                } else {
                    changeButton.disabled = true;
                }
            }
        }
    });
    </script>
</body>
</html>