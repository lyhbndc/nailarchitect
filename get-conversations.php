<?php
// api/get-conversations.php
// This file retrieves all conversations for an admin

// Start session and database connection
session_start();
require_once '../config/database.php'; // Adjust path as needed

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Get conversations with latest message and booking details
$query = "SELECT c.id, c.booking_id, c.user_id, c.status, c.updated_at, 
                 b.reference_id, b.service, b.date, b.time,
                 CASE WHEN b.user_id IS NOT NULL THEN CONCAT(u.first_name, ' ', u.last_name) ELSE b.name END as client_name,
                 (SELECT COUNT(*) FROM chat_messages WHERE conversation_id = c.id AND sender_type = 'user' AND `read` = 0) as unread_count,
                 (SELECT message FROM chat_messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message
          FROM chat_conversations c
          JOIN bookings b ON c.booking_id = b.id
          LEFT JOIN users u ON b.user_id = u.id
          ORDER BY c.updated_at DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

$conversations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $conversations[] = $row;
}

echo json_encode(['success' => true, 'conversations' => $conversations]);
?>