<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get date and time from request
if (!isset($_GET['date']) || !isset($_GET['time'])) {
    die(json_encode(['success' => false, 'message' => 'Missing parameters']));
}

$date = mysqli_real_escape_string($conn, $_GET['date']);
$time = mysqli_real_escape_string($conn, $_GET['time']);

// Check if date and time are already booked
$query = "SELECT COUNT(*) as count FROM bookings WHERE date = ? AND time = ? AND status != 'cancelled'";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $date, $time);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Return result
if ($row['count'] > 0) {
    // Time slot is already booked
    echo json_encode(['success' => true, 'available' => false]);
} else {
    // Time slot is available
    echo json_encode(['success' => true, 'available' => true]);
}

// Close database connection
mysqli_close($conn);
?>