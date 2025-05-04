<?php
// api/send-message.php
// This file handles sending a new message

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
$conversation_id = isset($_POST['conversation_id']) ? mysqli_real_escape_string($conn, $_POST['conversation_id']) : null;
$message_text = isset($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : null;

// Validate request data
if (!$conversation_id || !$message_text) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Check if conversation exists
$check_query = "SELECT * FROM chat_conversations WHERE id = $conversation_id";
$check_result = mysqli_query($conn, $check_query);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Conversation not found']);
    exit;
}

// Begin transaction
mysqli_begin_transaction($conn);

try {
    // Insert message
    $insert_message_query = "INSERT INTO chat_messages (conversation_id, sender_id, sender_type, message) 
                            VALUES ($conversation_id, {$_SESSION['admin_id']}, 'admin', '$message_text')";
    
    if (!mysqli_query($conn, $insert_message_query)) {
        throw new Exception("Failed to insert message: " . mysqli_error($conn));
    }
    
    $message_id = mysqli_insert_id($conn);
    $has_attachment = false;
    
    // Handle file upload if present
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $upload_dir = '../uploads/chat/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = $_FILES['attachment']['name'];
        $file_tmp = $_FILES['attachment']['tmp_name'];
        $file_size = $_FILES['attachment']['size'];
        $file_type = $_FILES['attachment']['type'];
        
        // Generate unique filename
        $unique_name = uniqid() . '_' . $file_name;
        $file_path = $upload_dir . $unique_name;
        
        // Move uploaded file
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Insert attachment record
            $relative_path = 'uploads/chat/' . $unique_name;
            $insert_attachment_query = "INSERT INTO chat_attachments (message_id, file_name, file_path, file_size, file_type) 
                                       VALUES ($message_id, '$file_name', '$relative_path', $file_size, '$file_type')";
            
            if (!mysqli_query($conn, $insert_attachment_query)) {
                throw new Exception("Failed to insert attachment: " . mysqli_error($conn));
            }
            
            // Update message to indicate it has an attachment
            $update_message_query = "UPDATE chat_messages SET has_attachment = 1 WHERE id = $message_id";
            mysqli_query($conn, $update_message_query);
            
            $has_attachment = true;
        } else {
            throw new Exception("Failed to upload file");
        }
    }
    
    // Update conversation timestamp
    $update_conversation_query = "UPDATE chat_conversations SET updated_at = NOW() WHERE id = $conversation_id";
    
    if (!mysqli_query($conn, $update_conversation_query)) {
        throw new Exception("Failed to update conversation: " . mysqli_error($conn));
    }
    
    // Create notification for user
    $conversation_data = mysqli_fetch_assoc($check_result);
    $user_id = $conversation_data['user_id'];
    
    if ($user_id) {
        $notification_query = "INSERT INTO chat_notifications (user_id, conversation_id, message_id) 
                              VALUES ($user_id, $conversation_id, $message_id)";
        mysqli_query($conn, $notification_query);
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true, 
        'message_id' => $message_id,
        'has_attachment' => $has_attachment,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>