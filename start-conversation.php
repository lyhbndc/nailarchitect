<?php
// api/start-conversation.php
// This file handles starting a new conversation for a booking

// Start session and database connection
session_start();
require_once '../config/database.php'; // Adjust path as needed

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get request data
$booking_id = isset($_POST['booking_id']) ? mysqli_real_escape_string($conn, $_POST['booking_id']) : null;
$initial_message = isset($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : null;

// Validate request data
if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'Missing booking ID']);
    exit;
}

// Check if booking exists and get user_id
$booking_query = "SELECT id, user_id FROM bookings WHERE id = $booking_id";
$booking_result = mysqli_query($conn, $booking_query);

if (!$booking_result || mysqli_num_rows($booking_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit;
}

$booking = mysqli_fetch_assoc($booking_result);
$user_id = $booking['user_id'];

// Check if conversation already exists for this booking
$check_query = "SELECT id FROM chat_conversations WHERE booking_id = $booking_id";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    $existing = mysqli_fetch_assoc($check_result);
    echo json_encode([
        'success' => true, 
        'conversation_id' => $existing['id'],
        'message' => 'Conversation already exists'
    ]);
    exit;
}

// Begin transaction
mysqli_begin_transaction($conn);

try {
    // Create new conversation
    $insert_conversation_query = "INSERT INTO chat_conversations (booking_id, user_id) 
                                 VALUES ($booking_id, " . ($user_id ? $user_id : "NULL") . ")";
    
    if (!mysqli_query($conn, $insert_conversation_query)) {
        throw new Exception("Failed to create conversation: " . mysqli_error($conn));
    }
    
    $conversation_id = mysqli_insert_id($conn);
    
    // If there's an initial message, add it
    if ($initial_message) {
        $insert_message_query = "INSERT INTO chat_messages (conversation_id, sender_id, sender_type, message) 
                                VALUES ($conversation_id, {$_SESSION['admin_id']}, 'admin', '$initial_message')";
        
        if (!mysqli_query($conn, $insert_message_query)) {
            throw new Exception("Failed to insert message: " . mysqli_error($conn));
        }
        
        $message_id = mysqli_insert_id($conn);
        
        // Create notification for user if user_id exists
        if ($user_id) {
            $notification_query = "INSERT INTO chat_notifications (user_id, conversation_id, message_id) 
                                  VALUES ($user_id, $conversation_id, $message_id)";
            mysqli_query($conn, $notification_query);
        }
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true, 
        'conversation_id' => $conversation_id,
        'message' => 'Conversation started successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>