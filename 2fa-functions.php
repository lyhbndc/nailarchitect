<?php
// 2fa-functions.php - 2FA helper functions

require_once 'google-authenticator.php';

/**
 * Initialize 2FA for a user/admin
 */
function initialize2FA($conn, $userId = null, $adminId = null) {
    $ga = new GoogleAuthenticator();
    $secret = $ga->createSecret();
    
    // Check if 2FA entry already exists
    if ($userId !== null) {
        $query = "SELECT * FROM user_2fa WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        $id = $userId;
    } else {
        $query = "SELECT * FROM user_2fa WHERE admin_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        $id = $adminId;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Update existing entry
        if ($userId !== null) {
            $updateQuery = "UPDATE user_2fa SET secret = ? WHERE user_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "si", $secret, $userId);
        } else {
            $updateQuery = "UPDATE user_2fa SET secret = ? WHERE admin_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "si", $secret, $adminId);
        }
        mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);
    } else {
        // Create new entry
        $insertQuery = "INSERT INTO user_2fa (user_id, admin_id, secret) VALUES (?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($insertStmt, "iis", $userId, $adminId, $secret);
        mysqli_stmt_execute($insertStmt);
        mysqli_stmt_close($insertStmt);
    }
    
    mysqli_stmt_close($stmt);
    return $secret;
}

/**
 * Get 2FA secret for user/admin
 */
function get2FASecret($conn, $userId = null, $adminId = null) {
    if ($userId !== null) {
        $query = "SELECT secret FROM user_2fa WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        $id = $userId;
    } else {
        $query = "SELECT secret FROM user_2fa WHERE admin_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        $id = $adminId;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['secret'];
    }
    
    mysqli_stmt_close($stmt);
    return null;
}

/**
 * Enable 2FA for user/admin
 */
function enable2FA($conn, $userId = null, $adminId = null) {
    if ($userId !== null) {
        $query = "UPDATE user_2fa SET enabled = 1 WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        $id = $userId;
    } else {
        $query = "UPDATE user_2fa SET enabled = 1 WHERE admin_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        $id = $adminId;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * Disable 2FA for user/admin
 */
function disable2FA($conn, $userId = null, $adminId = null) {
    if ($userId !== null) {
        $query = "UPDATE user_2fa SET enabled = 0 WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        $id = $userId;
    } else {
        $query = "UPDATE user_2fa SET enabled = 0 WHERE admin_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        $id = $adminId;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * Check if 2FA is enabled
 */
function is2FAEnabled($conn, $userId = null, $adminId = null) {
    if ($userId !== null) {
        $query = "SELECT enabled FROM user_2fa WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $userId);
    } else if ($adminId !== null) {
        $query = "SELECT enabled FROM user_2fa WHERE admin_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $adminId);
    } else {
        return false;
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['enabled'] == 1;
    }
    
    mysqli_stmt_close($stmt);
    return false;
}

/**
 * Verify 2FA code
 */
function verify2FACode($conn, $code, $userId = null, $adminId = null) {
    $secret = get2FASecret($conn, $userId, $adminId);
    
    if (!$secret) {
        return false;
    }
    
   $ga = new GoogleAuthenticator();
    $isValid = $ga->verifyCode($secret, $code);
    
    // Log attempt
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $logQuery = "INSERT INTO 2fa_attempts (user_id, admin_id, ip_address, success) VALUES (?, ?, ?, ?)";
    $logStmt = mysqli_prepare($conn, $logQuery);
    $success = $isValid ? 1 : 0;
    mysqli_stmt_bind_param($logStmt, "iisi", $userId, $adminId, $ip, $success);
    mysqli_stmt_execute($logStmt);
    mysqli_stmt_close($logStmt);
    
    return $isValid;
}

/**
 * Generate backup codes
 */
function generateBackupCodes($count = 8) {
    $codes = [];
    for ($i = 0; $i < $count; $i++) {
        $codes[] = strtoupper(bin2hex(random_bytes(4)));
    }
    return $codes;
}

/**
 * Store backup codes
 */
function storeBackupCodes($conn, $codes, $userId = null, $adminId = null) {
    $codesJson = json_encode($codes);
    
    if ($userId !== null) {
        $query = "UPDATE user_2fa SET backup_codes = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $codesJson, $userId);
    } else {
        $query = "UPDATE user_2fa SET backup_codes = ? WHERE admin_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $codesJson, $adminId);
    }
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * Verify backup code
 */
function verifyBackupCode($conn, $code, $userId = null, $adminId = null) {
    if ($userId !== null) {
        $query = "SELECT backup_codes FROM user_2fa WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        $id = $userId;
    } else {
        $query = "SELECT backup_codes FROM user_2fa WHERE admin_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        $id = $adminId;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $codes = json_decode($row['backup_codes'], true) ?: [];
        
        $key = array_search($code, $codes);
        if ($key !== false) {
            // Remove used code
            unset($codes[$key]);
            
            // Update remaining codes
            if ($userId !== null) {
                $updateQuery = "UPDATE user_2fa SET backup_codes = ? WHERE user_id = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                $remainingCodes = json_encode(array_values($codes));
                mysqli_stmt_bind_param($updateStmt, "si", $remainingCodes, $userId);
            } else {
                $updateQuery = "UPDATE user_2fa SET backup_codes = ? WHERE admin_id = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                $remainingCodes = json_encode(array_values($codes));
                mysqli_stmt_bind_param($updateStmt, "si", $remainingCodes, $adminId);
            }
            
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
            
            mysqli_stmt_close($stmt);
            return true;
        }
    }
    
    mysqli_stmt_close($stmt);
    return false;
}

/**
 * Get rate limit status
 */
function check2FAAttempts($conn, $userId = null, $adminId = null, $minutes = 15, $maxAttempts = 5) {
    if ($userId !== null) {
        $query = "SELECT COUNT(*) as attempts FROM 2fa_attempts WHERE user_id = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE) AND success = 0";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $userId, $minutes);
    } else {
        $query = "SELECT COUNT(*) as attempts FROM 2fa_attempts WHERE admin_id = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE) AND success = 0";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $adminId, $minutes);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return [
            'attempts' => $row['attempts'],
            'remaining' => $maxAttempts - $row['attempts'],
            'locked' => $row['attempts'] >= $maxAttempts
        ];
    }
    
    mysqli_stmt_close($stmt);
    return ['attempts' => 0, 'remaining' => $maxAttempts, 'locked' => false];
}
?>