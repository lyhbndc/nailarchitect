<?php
// Start session
session_start();

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin-dashboard.php');
    exit();
}

// Define admin credentials (replace with your preferred admin details)
$admin_username = 'admin';
$admin_password = 'admin123'; // In a real app, use password_hash() for storage

// Handle login form submission
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate credentials
    if ($username === $admin_username && $password === $admin_password) {
        // Set admin session
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_username'] = $username;
        
        // Redirect to dashboard
        header('Location: admin-dashboard.php');
        exit();
    } else {
        $error_message = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nail Architect - Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #F2E9E9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 400px;
            padding: 30px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: #e0c5b7;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .logo i {
            font-size: 28px;
            z-index: 1;
        }
        
        .logo::after {
            content: "";
            position: absolute;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #dcdcdc;
            right: -10px;
            bottom: -10px;
        }
        
        .login-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .login-subtitle {
            font-size: 14px;
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #E8D7D0;
            box-shadow: 0 0 5px rgba(232, 215, 208, 0.5);
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: #E8D7D0;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            background-color: #D9BBB0;
        }
        
        .error-message {
            background-color: #ffcdd2;
            color: #c62828;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-spa"></i>
            </div>
            <div class="login-title">Admin Login</div>
            <div class="login-subtitle">Nail Architect Management System</div>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" placeholder="Enter your username" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="login-btn">Log In</button>
        </form>
    </div>
</body>
</html>