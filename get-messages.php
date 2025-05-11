<?php
// api/get-messages.php
// This file retrieves messages for a specific conversation

// Start session and database connection
session_start();
require_once '../config/database.php'; // Adjust path as needed

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Get conversation ID from request
if (!isset($_GET['conversation_id']) || !is_numeric($_GET['conversation_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation ID']);
    exit;
}

$conversation_id = mysqli_real_escape_string($conn, $_GET['conversation_id']);

// Get conversation details
$conversation_query = "SELECT c.*, 
                              b.reference_id, b.service, b.date, b.time,
                              CASE WHEN b.user_id IS NOT NULL THEN CONCAT(u.first_name, ' ', u.last_name) ELSE b.name END as client_name
                       FROM chat_conversations c
                       JOIN bookings b ON c.booking_id = b.id
                       LEFT JOIN users u ON b.user_id = u.id
                       WHERE c.id = $conversation_id";

$conversation_result = mysqli_query($conn, $conversation_query);

if (!$conversation_result || mysqli_num_rows($conversation_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Conversation not found']);
    exit;
}

$conversation = mysqli_fetch_assoc($conversation_result);

// Get messages for the conversation
$messages_query = "SELECT m.*, a.file_name, a.file_path, a.file_type
                   FROM chat_messages m
                   LEFT JOIN chat_attachments a ON m.id = a.message_id
                   WHERE m.conversation_id = $conversation_id
                   ORDER BY m.created_at ASC";

$messages_result = mysqli_query($conn, $messages_query);

if (!$messages_result) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

// Mark all unread user messages as read
$update_read_query = "UPDATE chat_messages 
                     SET `read` = 1 
                     WHERE conversation_id = $conversation_id 
                     AND sender_type = 'user' 
                     AND `read` = 0";
mysqli_query($conn, $update_read_query);

$messages = [];
while ($row = mysqli_fetch_assoc($messages_result)) {
    // Group attachments with their messages
    $message_id = $row['id'];
    
    if (!isset($messages[$message_id])) {
        $messages[$message_id] = [
            'id' => $row['id'],
            'sender_type' => $row['sender_type'],
            'message' => $row['message'],
            'created_at' => $row['created_at'],
            'attachments' => []
        ];
    }
    
    if ($row['file_name']) {
        $messages[$message_id]['attachments'][] = [
            'file_name' => $row['file_name'],
            'file_path' => $row['file_path'],
            'file_type' => $row['file_type']
        ];
    }
}

// Convert to indexed array
$messages = array_values($messages);

echo json_encode([
    'success' => true, 
    'conversation' => $conversation, 
    'messages' => $messages
]);
?>