<?php
// Start session
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['signup'])) {
    if (isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['phone'], $_POST['password']) && 
        !empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['email']) && 
        !empty($_POST['phone']) && !empty($_POST['password'])) {

        $firstname = $_POST['first_name'];
        $lastname = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Check if the email already exists
        $check_email_query = "SELECT * FROM users WHERE email = '$email'";
        $email_result = mysqli_query($conn, $check_email_query);
        
        if (mysqli_num_rows($email_result) > 0) {
            echo "<script>
            if (confirm('Email already exists. Would you like to log in instead?')) {
                window.location.href = 'login.php';
            }
          </script>";
        } else {
            // Insert data into the users table
            $query = "INSERT INTO users (first_name, last_name, email, phone, password) 
                      VALUES ('$firstname', '$lastname', '$email', '$phone', '$hashedPassword')";
            $result = mysqli_query($conn, $query);

            if ($result) {
                // Account created successfully
                echo "<script>
                    alert('Account created successfully!');
                    window.location.href = 'login.php';
                </script>";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
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
    <title>Nail Architect - Sign Up</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins, sans-serif;
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
        
        .signup-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            animation: fadeIn 0.6s ease-out forwards;
            padding: 20px 0;
        }
        
        .signup-form-container {
            background-color: #e8d7d0;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 600px;
            animation: fadeIn 0.7s ease-out forwards;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .signup-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: #F2E9E9;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus {
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
        
        .password-strength, .match-status {
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .terms-container {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 20px 0;
            font-size: 13px;
        }
        
        .terms-container input {
            width: auto;
            margin-top: 4px;
        }
        
        .signup-button {
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
        
        .signup-button:hover {
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
        
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .signup-form-container {
                padding: 30px 20px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .footer-section {
                flex: 100%;
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
        
        <div class="signup-container">
            <div class="signup-form-container">
                <div class="signup-title">Create Account</div>
                
                <form id="signup-form" method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first-name">First Name</label>
                            <input type="text" id="first-name" name="first_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last-name">Last Name</label>
                            <input type="text" id="last-name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input-container">
                            <input type="password" id="password" name="password" required oninput="checkPasswordStrength()">
                            <i id="toggle-password-icon" class="fa fa-eye toggle-password" onclick="togglePasswordVisibility('password', 'toggle-password-icon')"></i>
                        </div>
                        <span id="password-strength" class="password-strength"></span>
                        <div class="password-requirements">
                            Password must be at least 8 characters with uppercase, lowercase, and number
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                        <div class="password-input-container">
                            <input type="password" id="confirm-password" name="confirm_password" required oninput="checkPasswordMatch()">
                            <i id="toggle-confirm-password-icon" class="fa fa-eye toggle-password" onclick="togglePasswordVisibility('confirm-password', 'toggle-confirm-password-icon')"></i>
                        </div>
                        <span id="match-status" class="match-status"></span>
                    </div>
                    
                    <div class="terms-container">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the Terms of Service and Privacy Policy</label>
                    </div>
                    
                    <button type="submit" name="signup" class="signup-button">Create Account</button>
                </form>
                
                <div class="login-link">
                    Already have an account? <span class="login-text" onclick="window.location.href='login.php'">Sign In</span>
                </div>
            </div>
            
            <div class="back-button" onclick="window.location.href='index.php'">← Back to Home</div>
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
            let strength = 'Weak';
            let color = 'red';

            if (password.length >= 8) {
                if (/[A-Z]/.test(password) && /[a-z]/.test(password) && /\d/.test(password) && /[^A-Za-z0-9]/.test(password)) {
                    strength = 'Strong';
                    color = 'green';
                } else if ((/[A-Za-z]/.test(password) && /\d/.test(password)) || (/[A-Za-z]/.test(password) && /[^A-Za-z0-9]/.test(password))) {
                    strength = 'Medium';
                    color = 'orange';
                }
            }

            strengthIndicator.textContent = `Password is ${strength}`;
            strengthIndicator.style.color = color;
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const matchStatus = document.getElementById('match-status');

            if (!confirmPassword) {
                matchStatus.textContent = '';
                return;
            }

            if (password === confirmPassword) {
                matchStatus.textContent = 'Passwords match';
                matchStatus.style.color = 'green';
            } else {
                matchStatus.textContent = 'Passwords do not match';
                matchStatus.style.color = 'red';
            }
        }
        
        document.getElementById('signup-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const terms = document.getElementById('terms').checked;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('You must agree to the Terms of Service');
                return;
            }
            
            // Password validation
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            if (!passwordRegex.test(password)) {
                e.preventDefault();
                alert('Password must be at least 8 characters with uppercase, lowercase, and number');
                return;
            }
        });
    </script>
</body>
</html>