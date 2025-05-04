<?php
// chat.php - handles user message functionality

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'get_messages':
        // Get all messages for the current user
        $query = "SELECT id, subject, content, sender_id, read_status, created_at, 
                         has_attachment 
                  FROM messages 
                  WHERE user_id = ? 
                  ORDER BY created_at ASC";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $message_id = $row['id'];
            
            // Determine if message is from salon (admin) or user
            $sender_type = ($row['sender_id'] === null) ? 'salon' : 'user';
            
            $messages[] = [
                'id' => $row['id'],
                'subject' => $row['subject'],
                'content' => $row['content'],
                'sender_type' => $sender_type,
                'read_status' => $row['read_status'],
                'created_at' => $row['created_at'],
                'has_attachment' => $row['has_attachment'],
                'attachments' => []
            ];
        }
        
        // Get attachments for messages with has_attachment = 1
        if (!empty($messages)) {
            $message_ids = array_column($messages, 'id');
            $message_ids_str = implode(',', $message_ids);
            
            // Only run this query if we have message IDs and the message_attachments table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'message_attachments'");
            if ($table_check->num_rows > 0 && !empty($message_ids_str)) {
                $attach_query = "SELECT message_id, file_name, file_path, file_type 
                              FROM message_attachments 
                              WHERE message_id IN ($message_ids_str)";
                $attach_result = $conn->query($attach_query);
                
                if ($attach_result && $attach_result->num_rows > 0) {
                    while ($attach = $attach_result->fetch_assoc()) {
                        // Find the message this attachment belongs to
                        foreach ($messages as &$message) {
                            if ($message['id'] == $attach['message_id']) {
                                $message['attachments'][] = [
                                    'file_name' => $attach['file_name'],
                                    'file_path' => $attach['file_path'],
                                    'file_type' => $attach['file_type']
                                ];
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        echo json_encode(['success' => true, 'messages' => $messages]);
        break;
        
    case 'send_message':
        // Validate input data
        if (!isset($_POST['subject']) || !isset($_POST['content'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }
        
        $subject = mysqli_real_escape_string($conn, $_POST['subject']);
        $content = mysqli_real_escape_string($conn, $_POST['content']);
        $current_time = date('Y-m-d H:i:s');
        $has_attachment = 0;
        
        // Begin transaction for message and possible attachment
        mysqli_begin_transaction($conn);
        
        try {
            // Check if message_attachments table exists, create if not
            $table_check = $conn->query("SHOW TABLES LIKE 'message_attachments'");
            if ($table_check->num_rows == 0) {
                $create_table = "CREATE TABLE IF NOT EXISTS `message_attachments` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `message_id` int(11) NOT NULL,
                    `file_name` varchar(255) NOT NULL,
                    `file_path` varchar(255) NOT NULL,
                    `file_size` int(11) NOT NULL,
                    `file_type` varchar(100) NOT NULL,
                    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id`),
                    KEY `message_id` (`message_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
                
                if (!$conn->query($create_table)) {
                    throw new Exception("Failed to create message_attachments table: " . $conn->error);
                }
                
                // Add foreign key if users table exists
                $alter_table = "ALTER TABLE `message_attachments` 
                               ADD CONSTRAINT `message_attachments_ibfk_1` 
                               FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) 
                               ON DELETE CASCADE;";
                $conn->query($alter_table);
            }
            
            // Check if has_attachment column exists in messages table, add if not
            $column_check = $conn->query("SHOW COLUMNS FROM `messages` LIKE 'has_attachment'");
            if ($column_check->num_rows == 0) {
                $add_column = "ALTER TABLE `messages` 
                              ADD COLUMN `has_attachment` TINYINT(1) 
                              NOT NULL DEFAULT 0 AFTER `content`;";
                if (!$conn->query($add_column)) {
                    throw new Exception("Failed to add has_attachment column: " . $conn->error);
                }
            }
            
            // Insert message as sent by the user
            $query = "INSERT INTO messages (user_id, sender_id, subject, content, has_attachment, read_status, created_at) 
                      VALUES (?, ?, ?, ?, ?, 0, ?)";
                      
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iissis", $user_id, $user_id, $subject, $content, $has_attachment, $current_time);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to send message: " . $stmt->error);
            }
            
            $message_id = $stmt->insert_id;
            
            // Handle file upload if present
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
                $upload_dir = 'uploads/messages/';
                
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
                    $attach_query = "INSERT INTO message_attachments (message_id, file_name, file_path, file_size, file_type) 
                                     VALUES (?, ?, ?, ?, ?)";
                    
                    $attach_stmt = $conn->prepare($attach_query);
                    $attach_stmt->bind_param("issis", $message_id, $file_name, $file_path, $file_size, $file_type);
                    
                    if (!$attach_stmt->execute()) {
                        throw new Exception("Failed to save attachment: " . $attach_stmt->error);
                    }
                    
                    // Update message to indicate it has an attachment
                    $update_query = "UPDATE messages SET has_attachment = 1 WHERE id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("i", $message_id);
                    
                    if (!$update_stmt->execute()) {
                        throw new Exception("Failed to update message: " . $update_stmt->error);
                    }
                } else {
                    throw new Exception("Failed to upload file");
                }
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'mark_read':
        // Validate input data
        if (!isset($_POST['message_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing message ID']);
            exit();
        }
        
        $message_id = mysqli_real_escape_string($conn, $_POST['message_id']);
        
        // Update message read status
        $query = "UPDATE messages SET read_status = 1 
                  WHERE id = ? AND user_id = ?";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $message_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark message as read: ' . $stmt->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// Close database connection
mysqli_close($conn);
?>