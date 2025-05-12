<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get admin ID from query parameter
$admin_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($admin_id > 0) {
    $query = "SELECT * FROM admin_users WHERE id = $admin_id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        
        // Remove password from response for security
        unset($admin['password']);
        
        // Return as JSON
        header('Content-Type: application/json');
        echo json_encode($admin);
    } else {
        // Admin not found
        http_response_code(404);
        echo json_encode(['error' => 'Admin not found']);
    }
} else {
    // Invalid ID
    http_response_code(400);
    echo json_encode(['error' => 'Invalid admin ID']);
}

mysqli_close($conn);
?>