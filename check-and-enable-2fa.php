<?php
// check-and-enable-2fa.php - Check and enable 2FA for a specific user
require_once '2fa-functions.php';
require_once 'google-authenticator.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set the user ID you want to check (from your error, it's user ID 7)
$userId = 7;

echo "<h2>2FA Status Check for User ID: $userId</h2>";

// First, let's check if the user exists
$userQuery = "SELECT * FROM users WHERE id = ?";
$userStmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($userStmt, "i", $userId);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);

if ($userRow = mysqli_fetch_assoc($userResult)) {
    echo "<p>User found: " . $userRow['first_name'] . " " . $userRow['last_name'] . " (" . $userRow['email'] . ")</p>";
} else {
    die("User not found!");
}
mysqli_stmt_close($userStmt);

// Check if user has 2FA record
$checkQuery = "SELECT * FROM user_2fa WHERE user_id = ?";
$checkStmt = mysqli_prepare($conn, $checkQuery);
mysqli_stmt_bind_param($checkStmt, "i", $userId);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

if ($row = mysqli_fetch_assoc($checkResult)) {
    echo "<h3>Current 2FA Status:</h3>";
    echo "<p>Secret: " . ($row['secret'] ? 'EXISTS' : 'NONE') . "</p>";
    echo "<p>Enabled: " . ($row['enabled'] ? 'YES' : 'NO') . "</p>";
    echo "<p>Backup Codes: " . ($row['backup_codes'] ? 'SET' : 'NONE') . "</p>";
    
    if (!$row['enabled']) {
        echo "<h3>Enabling 2FA...</h3>";
        
        // Enable 2FA
        $enableQuery = "UPDATE user_2fa SET enabled = 1 WHERE user_id = ?";
        $enableStmt = mysqli_prepare($conn, $enableQuery);
        mysqli_stmt_bind_param($enableStmt, "i", $userId);
        
        if (mysqli_stmt_execute($enableStmt)) {
            echo "<p style='color: green;'>✓ 2FA has been enabled!</p>";
            
            // Show QR code
            $ga = new GoogleAuthenticator();
            $qrUrl = $ga->getQRCodeGoogleUrl($userRow['email'], $row['secret'], 'Nail Architect');
            echo "<h3>QR Code:</h3>";
            echo "<img src='$qrUrl' alt='QR Code'>";
            echo "<p>Secret Key: " . $row['secret'] . "</p>";
        } else {
            echo "<p style='color: red;'>Failed to enable 2FA</p>";
        }
        mysqli_stmt_close($enableStmt);
    } else {
        echo "<p style='color: green;'>2FA is already enabled!</p>";
        
        // Show QR code
        $ga = new GoogleAuthenticator();
        $qrUrl = $ga->getQRCodeGoogleUrl($userRow['email'], $row['secret'], 'Nail Architect');
        echo "<h3>QR Code:</h3>";
        echo "<img src='$qrUrl' alt='QR Code'>";
        echo "<p>Secret Key: " . $row['secret'] . "</p>";
    }
} else {
    echo "<h3>No 2FA record found. Creating one...</h3>";
    
    // Initialize and enable 2FA
    $secret = initialize2FA($conn, $userId, null);
    
    if ($secret) {
        echo "<p style='color: green;'>✓ 2FA initialized with secret: $secret</p>";
        
        // Enable it
        if (enable2FA($conn, $userId, null)) {
            echo "<p style='color: green;'>✓ 2FA has been enabled!</p>";
            
            // Generate backup codes
            $backupCodes = generateBackupCodes();
            if (storeBackupCodes($conn, $backupCodes, $userId, null)) {
                echo "<h3>Backup Codes:</h3>";
                echo "<ul>";
                foreach ($backupCodes as $code) {
                    echo "<li>$code</li>";
                }
                echo "</ul>";
            }
            
            // Show QR code
            $ga = new GoogleAuthenticator();
            $qrUrl = $ga->getQRCodeGoogleUrl($userRow['email'], $secret, 'Nail Architect');
            echo "<h3>QR Code:</h3>";
            echo "<img src='$qrUrl' alt='QR Code'>";
            echo "<p>Secret Key: $secret</p>";
        }
    }
}

mysqli_stmt_close($checkStmt);
mysqli_close($conn);

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Scan the QR code with Google Authenticator app</li>";
echo "<li>Log out from your account</li>";
echo "<li>Try logging in again - you should now see the 2FA verification page!</li>";
echo "</ol>";
?>