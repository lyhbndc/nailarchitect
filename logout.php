<?php
session_start();

// Check who is logging out (admin or client)
$userType = '';
$redirectUrl = '';

if (isset($_SESSION['admin_id'])) {
    $userType = 'admin';
    $redirectUrl = 'admin-login.php';
} elseif (isset($_SESSION['user_id'])) {
    $userType = 'client';
    $redirectUrl = 'login.php';
}

// If logout is confirmed via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    if ($userType === 'admin') {
        // Admin logout
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_logged_in']);
    } else {
        // Client logout
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['logged_in']);
    }
    
    // Destroy the session
    session_destroy();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Redirect to appropriate login page
    header('Location: ' . $redirectUrl);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Nail Architect</title>
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
            position: relative;
        }
        
        /* Background overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
        
        .logout-container {
            background-color: #E8D7D0;
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            text-align: center;
            max-width: 440px;
            width: 90%;
            position: relative;
            z-index: 2;
        }
        
        .icon-container {
            margin-bottom: 30px;
            position: relative;
            display: inline-block;
        }
        
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #D9BBB0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            position: relative;
        }
        
        .icon-circle::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.3);
            bottom: -10px;
            right: -10px;
            z-index: -1;
        }
        
        .logout-icon {
            font-size: 36px;
            color: #333;
        }
        
        h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 28px;
            font-weight: 600;
        }
        
        .user-info-box {
            background-color: rgba(255, 255, 255, 0.4);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .user-info-box p {
            margin: 5px 0;
            color: #333;
            font-size: 16px;
        }
        
        .user-info-box p:first-child {
            font-weight: 600;
        }
        
        .question {
            color: #666;
            margin-bottom: 35px;
            font-size: 18px;
            line-height: 1.6;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn {
            padding: 14px 35px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-logout {
            background-color: #eb5c50;
            color: white;
        }
        
        .btn-logout:hover {
            background-color: #d84a3f;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(235, 92, 80, 0.3);
        }
        
        .btn-cancel {
            background-color: #8b9a9b;
            color: white;
        }
        
        .btn-cancel:hover {
            background-color: #758586;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(139, 154, 155, 0.3);
        }
        
        /* Animation */
        .logout-container {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 480px) {
            .logout-container {
                padding: 40px 25px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .question {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="icon-container">
            <div class="icon-circle">
                <i class="fas fa-sign-out-alt logout-icon"></i>
            </div>
        </div>
        
        <h2>Confirm Logout</h2>
        
        <?php if ($userType === 'admin'): ?>
            <div class="user-info-box">
                <p>Admin Account</p>
                <p>Session will be terminated</p>
            </div>
            <p class="question">Are you sure you want to log out of the admin dashboard?</p>
        <?php elseif ($userType === 'client'): ?>
            <div class="user-info-box">
                <p>Client Account</p>
                <p><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'; ?></p>
            </div>
            <p class="question">Are you sure you want to log out of your account?</p>
        <?php else: ?>
            <div class="user-info-box">
                <p>No Active Session</p>
                <p>You are not logged in</p>
            </div>
            <p class="question">You are not currently logged in.</p>
        <?php endif; ?>
        
        <?php if ($userType): ?>
            <form method="POST" action="">
                <div class="button-group">
                    <button type="submit" name="confirm_logout" value="1" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Yes, Logout
                    </button>
                    <button type="button" onclick="window.history.back()" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="button-group">
                <a href="login.php" class="btn btn-logout">Go to Client Login</a>
                <a href="admin-login.php" class="btn btn-cancel">Go to Admin Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>