<?php
// update_booking.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$response = ['success' => false, 'message' => 'Invalid request'];

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    $response['message'] = "Database connection failed";
    echo json_encode($response);
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Check if this is a cancellation request
if (isset($_POST['action']) && $_POST['action'] == 'cancel' && isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    
    // Verify the booking belongs to this user
    $check_query = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $booking_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 1) {
        // Update booking status to cancelled
        $update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("i", $booking_id);
        
        if ($update_stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Booking cancelled successfully";
        } else {
            $response['message'] = "Error cancelling booking: " . $conn->error;
        }
    } else {
        $response['message'] = "Booking not found or not authorized";
    }
}

// Check if this is a reschedule request
if (isset($_POST['action']) && $_POST['action'] == 'reschedule' && isset($_POST['booking_id']) && isset($_POST['new_date']) && isset($_POST['new_time'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_date = $_POST['new_date'];
    $new_time = $_POST['new_time'];
    
    // Validate date and time
    if (strtotime($new_date) < strtotime('today')) {
        $response['message'] = "Cannot reschedule to a past date";
        echo json_encode($response);
        exit();
    }
    
    // Verify the booking belongs to this user
    $check_query = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $booking_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 1) {
        // Update booking date and time
        $update_query = "UPDATE bookings SET date = ?, time = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $new_date, $new_time, $booking_id);
        
        if ($update_stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Booking rescheduled successfully";
        } else {
            $response['message'] = "Error rescheduling booking: " . $conn->error;
        }
    } else {
        $response['message'] = "Booking not found or not authorized";
    }
}

// Close connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>