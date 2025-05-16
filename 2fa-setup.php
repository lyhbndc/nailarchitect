<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Include necessary files
require_once '2fa-functions.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Initialize variables
$qrCodeUrl = '';
$secret = '';
$errorMsg = '';
$successMsg = '';
$backupCodes = array();
$is2FAEnabled = false;

// Check current 2FA status
$check2fa_query = "SELECT * FROM user_2fa WHERE user_id = ?";
$check2fa_stmt = $conn->prepare($check2fa_query);
$check2fa_stmt->bind_param("i", $user_id);
$check2fa_stmt->execute();
$check2fa_result = $check2fa_stmt->get_result();

if ($check2fa_result->num_rows > 0) {
    $row = $check2fa_result->fetch_assoc();
    $is2FAEnabled = $row['enabled'] == 1;
    $secret = $row['secret']; // Changed from secret_key to secret
    
    // Get backup codes if they exist
    if (isset($row['backup_codes']) && !empty($row['backup_codes'])) {
        $backup_codes_json = $row['backup_codes'];
        $stored_backup_codes = json_decode($backup_codes_json, true);
        
        // If there are backup codes in session (from enabling 2FA), use those
        if (isset($_SESSION['backup_codes'])) {
            $backupCodes = $_SESSION['backup_codes'];
            unset($_SESSION['backup_codes']); // Clear from session after using
        }
    }
} else {
    // Generate a new secret key for first-time setup
    $secret = initialize2FA($conn, $user_id, null);
}

// Get user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Generate QR code URL
$ga = new GoogleAuthenticator();
$appName = "Nail Architect";
$userIdentifier = $user['email'];
$qrCodeUrl = $ga->getQRCodeGoogleUrl($userIdentifier, $secret, $appName);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Enable 2FA
    if (isset($_POST['enable_2fa'])) {
        $verificationCode = trim($_POST['verification_code']);
        
        // Verify the code - use wider discrepancy window (4) instead of 2
        $checkResult = $ga->verifyCode($secret, $verificationCode, 4);
        
        if ($checkResult) {
            // Generate backup codes
            $backupCodes = generateBackupCodes();
            
            // Store backup codes
            if (storeBackupCodes($conn, $backupCodes, $user_id, null)) {
                // Update 2FA status to enabled
                if (enable2FA($conn, $user_id, null)) {
                    $is2FAEnabled = true;
                    $successMsg = "Two-factor authentication has been successfully enabled!";
                    
                    // Store backup codes in session for display
                    $_SESSION['backup_codes'] = $backupCodes;
                } else {
                    $errorMsg = "Error enabling two-factor authentication. Please try again.";
                }
            } else {
                $errorMsg = "Error storing backup codes. Please try again.";
            }
        } else {
            $errorMsg = "Invalid verification code. Please try again.";
        }
    }
    
    // Disable 2FA
    if (isset($_POST['disable_2fa'])) {
        $verificationCode = trim($_POST['verification_code']);
        
        // Verify the code - use wider discrepancy window (4) instead of 2
        $checkResult = $ga->verifyCode($secret, $verificationCode, 4);
        
        if ($checkResult) {
            // Update 2FA status
            if (disable2FA($conn, $user_id, null)) {
                $is2FAEnabled = false;
                $successMsg = "Two-factor authentication has been disabled.";
            } else {
                $errorMsg = "Error disabling two-factor authentication. Please try again.";
            }
        } else {
            $errorMsg = "Invalid verification code. Please try again.";
        }
    }
}

// Determine page mode
$mode = isset($_GET['action']) ? $_GET['action'] : 'setup';

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication Setup - Nail Architect</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="bg-gradient.css">
    <link rel="icon" type="image/png" href="Assets/favicon.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins;
        }
        
        body {
            background-color: #f2e9e9;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container{
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
        
        .incontainer {
            max-width: 800px;
            margin: 30px auto;
            padding: 40px;
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1), 0 2px 8px rgba(0, 0, 0, 0.05), inset 0 1px 2px rgba(255, 255, 255, 0.3);
        }
        
        .page-title {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            font-size: 16px;
            margin-bottom: 25px;
            color: #666;
        }
        
        .setup-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .setup-card {
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .qr-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px 0;
        }
        
        .qr-code {
            padding: 10px;
            background-color: white;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .qr-code img {
            max-width: 200px;
        }
        
        .secret-key {
            font-family: monospace;
            font-size: 16px;
            background-color: #f0f0f0;
            padding: 8px 12px;
            border-radius: 5px;
            margin-top: 10px;
            user-select: all;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: #ffffff;
            font-family: Poppins;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        
        .submit-button {
            padding: 12px 24px;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            margin-top: 20px;
            font-weight: bold;
            color: black;
        }
        
        .submit-button:hover {
            background: linear-gradient(to right, #d98d8d, #ce7878);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: linear-gradient(to right, #ffcdd2, #ef9a9a);
        }
        
        .btn-danger:hover {
            background: linear-gradient(to right, #ef9a9a, #e57373);
        }
        
        .error-message, .success-message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error-message {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .success-message {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .backup-codes-container {
            margin-top: 20px;
            text-align: center;
        }
        
        .backup-codes-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        
        .backup-code {
            font-family: monospace;
            font-size: 16px;
            padding: 8px;
            background-color: #f0f0f0;
            border-radius: 5px;
            text-align: center;
        }
        
        .copy-codes-btn {
            background-color: #e0e0e0;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        
        .copy-codes-btn:hover {
            background-color: #d0d0d0;
        }
        
        .print-codes-btn {
            background-color: #e0e0e0;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            margin-left: 10px;
            transition: all 0.3s ease;
        }
        
        .print-codes-btn:hover {
            background-color: #d0d0d0;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        
        .cancel-button {
            padding: 12px 24px;
            background-color: #e0e0e0;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            display: block;
            text-align: center;
            text-decoration: none;
            color: #333;
            margin-top: 20px;
        }
        
        .cancel-button:hover {
            background-color: #d0d0d0;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 15px;
            position: relative;
        }
        
        .step.active {
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            color: white;
        }
        
        .step::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 2px;
            background-color: #e0e0e0;
            right: -30px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .step:last-child::after {
            display: none;
        }
        
        .step-title {
            position: absolute;
            top: 35px;
            white-space: nowrap;
            font-size: 12px;
            color: #666;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .backup-codes-container, .backup-codes-container * {
                visibility: visible;
            }
            .backup-codes-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .copy-codes-btn, .print-codes-btn {
                display: none;
            }
        }
        
        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        
        .back-link {
            padding: 8px 16px;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            border-radius: 20px;
            text-decoration: none;
            color: black;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: linear-gradient(to right, #d98d8d, #ce7878);
            transform: translateY(-2px);
        }

        /* Debug styles */
        .debug-info {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="gradient-overlay"></div>
    <div class="background-pattern"></div>
    <div class="swirl-pattern"></div>
    <div class="polish-drips"></div>
    
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

    <div class="incontainer">
        <h1 class="page-title">Two-Factor Authentication</h1>
        <p class="page-subtitle">
            <?php 
            if ($mode === 'disable') {
                echo 'Disable two-factor authentication for your account';
            } elseif ($mode === 'manage') {
                echo 'Manage your two-factor authentication settings';
            } else {
                echo 'Set up two-factor authentication for added security';
            }
            ?>
        </p>
        
        <?php if (!empty($errorMsg)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($successMsg)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>
        
        <?php if ($mode === 'disable'): ?>
            <!-- Disable 2FA Form -->
            <div class="setup-container">
                <div class="setup-card">
                    <h2 class="card-title">Disable Two-Factor Authentication</h2>
                    <p>To disable two-factor authentication, please enter the verification code from your authenticator app.</p>
                    
                    <form method="post" action="2fa-setup.php?action=disable">
                        <div class="form-group">
                            <label for="verification-code">Verification Code:</label>
                            <input type="text" id="verification-code" name="verification_code" placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" required>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="members-lounge.php" class="cancel-button">Cancel</a>
                            <button type="submit" name="disable_2fa" class="submit-button btn-danger">Disable 2FA</button>
                        </div>
                    </form>
                </div>
            </div>
            
        <?php elseif ($mode === 'manage'): ?>
            <!-- Manage 2FA Settings -->
            <div class="setup-container">
                <div class="setup-card">
                    <h2 class="card-title">Your 2FA Settings</h2>
                    <p>Your two-factor authentication is currently <strong>enabled</strong>.</p>
                    
                    <div class="form-group">
                        <label>Your Recovery Backup Codes:</label>
                        <p>If you lose access to your authenticator app, you can use one of these backup codes to sign in. Each code can only be used once.</p>
                        
                        <div class="backup-codes-container" id="backup-codes-container">
                            <h3>Your Backup Codes</h3>
                            
                            <?php if (!empty($backupCodes)): ?>
                                <div class="backup-codes-list">
                                    <?php foreach ($backupCodes as $code): ?>
                                        <div class="backup-code"><?php echo htmlspecialchars($code); ?></div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <button type="button" class="copy-codes-btn" id="copy-codes-btn">Copy Codes</button>
                                <button type="button" class="print-codes-btn" id="print-codes-btn">Print Codes</button>
                                
                                <p><small>Keep these codes in a safe place and never share them with anyone.</small></p>
                            <?php else: ?>
                                <p>No backup codes are available. If you need to generate new backup codes, you'll need to disable and re-enable 2FA.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="members-lounge.php" class="cancel-button">Back to Account</a>
                        <a href="2fa-setup.php?action=disable" class="submit-button btn-danger">Disable 2FA</a>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Set up 2FA -->
            <div class="setup-container">
                <?php if (!$is2FAEnabled): ?>
                    <!-- Step 1: Set up authenticator app -->
                    <div class="step-indicator">
                        <div class="step active">
                            1
                            <span class="step-title">Set Up</span>
                        </div>
                        <div class="step">
                            2
                            <span class="step-title">Verify</span>
                        </div>
                        <div class="step">
                            3
                            <span class="step-title">Backup</span>
                        </div>
                    </div>
                
                    <div class="setup-card">
                        <h2 class="card-title">Set Up Authenticator App</h2>
                        <p>Follow these steps to set up two-factor authentication:</p>
                        
                        <ol>
                            <li>Download and install an authenticator app on your mobile device:
                                <ul>
                                    <li><a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Google Authenticator (Android)</a></li>
                                    <li><a href="https://apps.apple.com/us/app/google-authenticator/id388497605" target="_blank">Google Authenticator (iOS)</a></li>
                                    <li><a href="https://authy.com/download/" target="_blank">Authy (Android/iOS)</a></li>
                                </ul>
                            </li>
                            <li>Scan the QR code below with your authenticator app</li>
                            <li>Enter the 6-digit verification code provided by the app</li>
                        </ol>
                        
                        <div class="qr-container">
                            <div class="qr-code">
                                <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
                            </div>
                            
                            <p>If you can't scan the QR code, enter this code manually:</p>
                            <div class="secret-key"><?php echo $secret; ?></div>
                        </div>
                        
                        <form method="post" action="2fa-setup.php">
                            <div class="form-group">
                                <label for="verification-code">Verification Code:</label>
                                <input type="text" id="verification-code" name="verification_code" placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" required>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="members-lounge.php" class="cancel-button">Cancel</a>
                                <button type="submit" name="enable_2fa" class="submit-button">Verify & Enable</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Step 3: Show backup codes after successful setup -->
                    <div class="step-indicator">
                        <div class="step">
                            1
                            <span class="step-title">Set Up</span>
                        </div>
                        <div class="step">
                            2
                            <span class="step-title">Verify</span>
                        </div>
                        <div class="step active">
                            3
                            <span class="step-title">Backup Codes</span>
                        </div>
                    </div>
                    
                    <div class="setup-card">
                        <h2 class="card-title">Two-Factor Authentication Enabled!</h2>
                        <p>Your account is now protected with two-factor authentication. Each time you sign in, you'll need to enter a verification code from your authenticator app.</p>
                        
                        <div class="backup-codes-container" id="backup-codes-container">
                            <h3>Your Backup Codes</h3>
                            <p>If you lose access to your authenticator app, you can use one of these backup codes to sign in. Each code can only be used once.</p>
                            
                            <?php if (!empty($backupCodes)): ?>
                                <div class="backup-codes-list">
                                    <?php foreach ($backupCodes as $code): ?>
                                        <div class="backup-code"><?php echo htmlspecialchars($code); ?></div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <button type="button" class="copy-codes-btn" id="copy-codes-btn">Copy Codes</button>
                                <button type="button" class="print-codes-btn" id="print-codes-btn">Print Codes</button>
                                
                                <p><small>Keep these codes in a safe place and never share them with anyone.</small></p>
                            <?php else: ?>
                                <p>No backup codes are available. If you need to generate new backup codes, you'll need to disable and re-enable 2FA.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="members-lounge.php" class="submit-button">Back to Account</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Copy backup codes to clipboard
        document.getElementById('copy-codes-btn')?.addEventListener('click', function() {
            const backupCodes = Array.from(document.querySelectorAll('.backup-code'))
                .map(el => el.textContent.trim())
                .join('\n');
            
            navigator.clipboard.writeText(backupCodes)
                .then(() => {
                    alert('Backup codes copied to clipboard!');
                })
                .catch(err => {
                    console.error('Error copying text: ', err);
                    alert('Failed to copy backup codes. Please try again or copy them manually.');
                });
        });
        
        // Print backup codes
        document.getElementById('print-codes-btn')?.addEventListener('click', function() {
            window.print();
        });
    </script>
</body>
</html>