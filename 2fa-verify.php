<?php
// 2fa-verify.php - Google Authenticator verification
session_start();

require_once '2fa-functions.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$error_message = '';
$success_message = '';
$isAdmin = isset($_SESSION['2fa_admin']) && $_SESSION['2fa_admin'] === true;
$showBackupCodeInput = false;

// Check if user is in 2FA verification stage
if (!isset($_SESSION['2fa_required']) || $_SESSION['2fa_required'] !== true) {
    header('Location: ' . ($isAdmin ? 'admin-login.php' : 'login.php'));
    exit();
}

// Get user/admin ID
$userId = $isAdmin ? null : ($_SESSION['2fa_user_id'] ?? null);
$adminId = $isAdmin ? ($_SESSION['2fa_admin_id'] ?? null) : null;

// Check rate limiting
$attemptStatus = check2FAAttempts($conn, $userId, $adminId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($attemptStatus['locked']) {
        $error_message = 'Too many failed attempts. Please try again in 15 minutes.';
    } else {
        if (isset($_POST['verify_code'])) {
            $code = $_POST['code'] ?? '';
            
            if (verify2FACode($conn, $code, $userId, $adminId)) {
                // Successful verification
                if ($isAdmin) {
                    $_SESSION['admin_id'] = $_SESSION['2fa_admin_id'];
                    $_SESSION['admin_username'] = $_SESSION['2fa_admin_username'];
                    $_SESSION['admin_role'] = $_SESSION['2fa_admin_role'];
                    $_SESSION['admin_name'] = $_SESSION['2fa_admin_name'];
                    
                    // Clear 2FA session variables
                    unset($_SESSION['2fa_required']);
                    unset($_SESSION['2fa_admin']);
                    unset($_SESSION['2fa_admin_id']);
                    unset($_SESSION['2fa_admin_username']);
                    unset($_SESSION['2fa_admin_role']);
                    unset($_SESSION['2fa_admin_name']);
                    
                    // Update last login
                    $updateQuery = "UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
                    $updateStmt = mysqli_prepare($conn, $updateQuery);
                    mysqli_stmt_bind_param($updateStmt, "i", $adminId);
                    mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);
                    
                    header('Location: admin-dashboard.php');
                } else {
                    $_SESSION['user_id'] = $_SESSION['2fa_user_id'];
                    $_SESSION['user_name'] = $_SESSION['2fa_user_name'];
                    $_SESSION['user_email'] = $_SESSION['2fa_user_email'];
                    
                    // Clear 2FA session variables
                    unset($_SESSION['2fa_required']);
                    unset($_SESSION['2fa_admin']);
                    unset($_SESSION['2fa_user_id']);
                    unset($_SESSION['2fa_user_name']);
                    unset($_SESSION['2fa_user_email']);
                    
                    header('Location: members-lounge.php');
                }
                exit();
            } else {
                $attemptStatus = check2FAAttempts($conn, $userId, $adminId);
                $error_message = 'Invalid code. ' . $attemptStatus['remaining'] . ' attempts remaining.';
            }
        } elseif (isset($_POST['use_backup_code'])) {
            $showBackupCodeInput = true;
        } elseif (isset($_POST['verify_backup_code'])) {
            $backupCode = $_POST['backup_code'] ?? '';
            
            if (verifyBackupCode($conn, $backupCode, $userId, $adminId)) {
                // Successful backup code verification
                if ($isAdmin) {
                    $_SESSION['admin_id'] = $_SESSION['2fa_admin_id'];
                    $_SESSION['admin_username'] = $_SESSION['2fa_admin_username'];
                    $_SESSION['admin_role'] = $_SESSION['2fa_admin_role'];
                    $_SESSION['admin_name'] = $_SESSION['2fa_admin_name'];
                    
                    // Clear 2FA session variables
                    unset($_SESSION['2fa_required']);
                    unset($_SESSION['2fa_admin']);
                    unset($_SESSION['2fa_admin_id']);
                    unset($_SESSION['2fa_admin_username']);
                    unset($_SESSION['2fa_admin_role']);
                    unset($_SESSION['2fa_admin_name']);
                    
                    header('Location: admin-dashboard.php');
                } else {
                    $_SESSION['user_id'] = $_SESSION['2fa_user_id'];
                    $_SESSION['user_name'] = $_SESSION['2fa_user_name'];
                    $_SESSION['user_email'] = $_SESSION['2fa_user_email'];
                    
                    // Clear 2FA session variables
                    unset($_SESSION['2fa_required']);
                    unset($_SESSION['2fa_admin']);
                    unset($_SESSION['2fa_user_id']);
                    unset($_SESSION['2fa_user_name']);
                    unset($_SESSION['2fa_user_email']);
                    
                    header('Location: members-lounge.php');
                }
                exit();
            } else {
                $error_message = 'Invalid backup code.';
                $showBackupCodeInput = true;
            }
        }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification - Nail Architect</title>
    <link rel="icon" type="image/png" href="Assets/favicon.png">
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
        
        .verify-container {
            background-color: rgb(245, 207, 207);
            border-radius: 20px;
            padding: 50px 40px;
            border: 1px solid rgba(235, 184, 184, 0.3);
            box-shadow: 
                0 4px 16px rgba(0, 0, 0, 0.1),
                0 2px 8px rgba(0, 0, 0, 0.05),
                inset 0 1px 2px rgba(255, 255, 255, 0.3);
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
        
        .security-icon {
            font-size: 60px;
            color: #ae9389;
        }
        
        h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
            font-weight: bold;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .code-input {
            width: 250px;
            padding: 15px;
            border: none;
            border-radius: 8px;
            background-color: #F2E9E9;
            font-size: 24px;
            text-align: center;
            letter-spacing: 5px;
            font-weight: bold;
            transition: all 0.3s ease;
            margin: 20px auto;
            display: block;
        }
        
        .code-input:focus {
            outline: none;
            border-color: #333;
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(217, 187, 176, 0.2);
        }
        
        .backup-input {
            width: 200px;
            padding: 12px;
            font-size: 16px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .error-message, .success-message {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .error-message {
            background-color: rgba(239, 83, 80, 0.1);
            color: #c62828;
        }
        
        .success-message {
            background-color: rgba(76, 175, 80, 0.1);
            color: #2e7d32;
        }
        
        .verify-btn {
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .verify-btn:hover {
            background: linear-gradient(to right, #d98d8d, #ce7878);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(233, 164, 164, 0.3);
        }
        
        .verify-btn:focus {
            outline: none;
        }
        
        .alternative-section {
            margin-top: 30px;
            font-size: 14px;
            color: #666;
        }
        
        .alternative-btn {
            background: none;
            border: none;
            color: #ae9389;
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .alternative-btn:hover {
            color: #333;
        }
        
        .back-link {
            margin-top: 20px;
            font-size: 14px;
        }
        
        .back-link a {
            color: #666;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }
        
        .back-link a:hover {
            color: #333;
        }
        
        .info-text {
            background-color: #e8f4ff;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #0066cc;
        }
        
        /* Animation */
        .verify-container {
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
            .verify-container {
                padding: 40px 25px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .subtitle {
                font-size: 14px;
            }
            
            .code-input {
                width: 200px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="icon-container">
            <i class="fas fa-lock security-icon"></i>
        </div>
        
        <h2>Two-Factor Authentication</h2>
        <p class="subtitle">
            <?php if (!$showBackupCodeInput): ?>
                Enter the 6-digit code from your authenticator app
            <?php else: ?>
                Enter one of your backup codes
            <?php endif; ?>
        </p>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($attemptStatus['locked']): ?>
            <div class="error-message">
                <i class="fas fa-lock"></i>
                Account temporarily locked due to too many failed attempts. Please try again later.
            </div>
        <?php else: ?>
            <?php if (!$showBackupCodeInput): ?>
                <form method="POST" action="">
                    <input type="text" name="code" class="code-input" maxlength="6" 
                           pattern="[0-9]{6}" required autofocus 
                           placeholder="000000">
                    
                    <button type="submit" name="verify_code" class="verify-btn">
                        <i class="fas fa-check"></i> Verify Code
                    </button>
                </form>
                
                <div class="alternative-section">
                    Lost your phone?
                    <form method="POST" action="" style="display: inline;">
                        <button type="submit" name="use_backup_code" class="alternative-btn">
                            Use a backup code
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    Backup codes are single-use only
                </div>
                
                <form method="POST" action="">
                    <input type="text" name="backup_code" class="code-input backup-input" 
                           maxlength="8" required autofocus 
                           placeholder="XXXXXXXX">
                    
                    <button type="submit" name="verify_backup_code" class="verify-btn">
                        <i class="fas fa-key"></i> Verify Backup Code
                    </button>
                </form>
                
                <div class="alternative-section">
                    <a href="?">← Back to authenticator code</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="<?php echo $isAdmin ? 'admin-login.php' : 'login.php'; ?>">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</body>
</html>