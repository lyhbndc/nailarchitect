<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h1>Simple Token Validator</h1>";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    echo "<p>Token from URL: <strong>" . htmlspecialchars($token) . "</strong></p>";
    
    // Direct SQL query to check token
    $query = "SELECT * FROM password_resets WHERE token = '" . mysqli_real_escape_string($conn, $token) . "'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo "<p style='color:green; font-weight:bold;'>✅ SUCCESS: Token found in database!</p>";
        echo "<pre>";
        print_r($row);
        echo "</pre>";
        
        // Check expiration
        $now = time();
        $expires = strtotime($row['expires_at']);
        
        if ($expires > $now) {
            echo "<p style='color:green; font-weight:bold;'>✅ Token is still valid (not expired)</p>";
            echo "<p>Current time: " . date('Y-m-d H:i:s', $now) . "</p>";
            echo "<p>Expires at: " . date('Y-m-d H:i:s', $expires) . "</p>";
            echo "<p>Time left: " . round(($expires - $now) / 60) . " minutes</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>❌ Token has EXPIRED</p>";
            echo "<p>Current time: " . date('Y-m-d H:i:s', $now) . "</p>";
            echo "<p>Expired at: " . date('Y-m-d H:i:s', $expires) . "</p>";
            echo "<p>Expired " . round(($now - $expires) / 60) . " minutes ago</p>";
        }
        
        // Check if the user exists
        $user_id = $row['user_id'];
        $user_query = "SELECT id, first_name, email FROM users WHERE id = " . intval($user_id);
        $user_result = mysqli_query($conn, $user_query);
        
        if ($user_result && mysqli_num_rows($user_result) > 0) {
            $user_row = mysqli_fetch_assoc($user_result);
            echo "<p style='color:green; font-weight:bold;'>✅ User found: " . htmlspecialchars($user_row['first_name']) . " (ID: " . $user_row['id'] . ", Email: " . $user_row['email'] . ")</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>❌ User with ID " . $user_id . " not found!</p>";
        }
    } else {
        echo "<p style='color:red; font-weight:bold;'>❌ ERROR: No token found matching '" . htmlspecialchars($token) . "'</p>";
        
        // List all tokens for debugging
        $all_tokens = mysqli_query($conn, "SELECT * FROM password_resets");
        echo "<h2>All tokens in database:</h2>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>User ID</th><th>Token</th><th>Expires</th></tr>";
        
        while ($token_row = mysqli_fetch_assoc($all_tokens)) {
            echo "<tr>";
            echo "<td>" . $token_row['id'] . "</td>";
            echo "<td>" . $token_row['user_id'] . "</td>";
            echo "<td style='font-family: monospace; max-width: 300px; word-break: break-all;'>" . $token_row['token'] . "</td>";
            echo "<td>" . $token_row['expires_at'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
} else {
    echo "<p style='color:red;'>No token provided in URL. Use ?token=YOUR_TOKEN</p>";
    
    // Display all tokens anyway
    $all_tokens = mysqli_query($conn, "SELECT * FROM password_resets");
    echo "<h2>All tokens in database:</h2>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Token</th><th>Expires</th></tr>";
    
    while ($token_row = mysqli_fetch_assoc($all_tokens)) {
        echo "<tr>";
        echo "<td>" . $token_row['id'] . "</td>";
        echo "<td>" . $token_row['user_id'] . "</td>";
        echo "<td style='font-family: monospace; max-width: 300px; word-break: break-all;'>" . $token_row['token'] . "</td>";
        echo "<td>" . $token_row['expires_at'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

mysqli_close($conn);
?>