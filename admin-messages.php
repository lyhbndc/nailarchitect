<?php
// Start session and database connection
session_start();
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get all messages grouped by user
$messages_query = "SELECT m.user_id, u.first_name, u.last_name, u.email, 
                   COUNT(m.id) as message_count,
                   MAX(m.created_at) as last_message,
                   SUM(CASE WHEN m.sender_id IS NULL AND m.read_status = 0 THEN 1 ELSE 0 END) as unread_count
                   FROM messages m
                   JOIN users u ON m.user_id = u.id
                   GROUP BY m.user_id, u.first_name, u.last_name, u.email
                   ORDER BY last_message DESC";
$messages_result = mysqli_query($conn, $messages_query);

// Get specific conversation if user_id is provided
$conversation = [];
if (isset($_GET['user_id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
    
    // Get user details
    $user_query = "SELECT * FROM users WHERE id = '$user_id'";
    $user_result = mysqli_query($conn, $user_query);
    $user = mysqli_fetch_assoc($user_result);
    
    // Get all messages for this user
    $conversation_query = "SELECT m.*, 
                          CASE WHEN m.sender_id IS NULL THEN 'salon' ELSE 'user' END as sender_type
                          FROM messages m
                          WHERE m.user_id = '$user_id'
                          ORDER BY m.created_at ASC";
    $conversation_result = mysqli_query($conn, $conversation_query);
    
    while ($message = mysqli_fetch_assoc($conversation_result)) {
        // Get message attachments if any
        $message['attachments'] = [];
        
        if ($message['has_attachment']) {
            $attachments_query = "SELECT * FROM message_attachments WHERE message_id = '" . $message['id'] . "'";
            $attachments_result = mysqli_query($conn, $attachments_query);
            
            while ($attachment = mysqli_fetch_assoc($attachments_result)) {
                $message['attachments'][] = $attachment;
            }
        }
        
        $conversation[] = $message;
    }
    
    // Mark all salon messages as read
    $mark_read_query = "UPDATE messages SET read_status = 1 
                       WHERE user_id = '$user_id' AND sender_id IS NULL AND read_status = 0";
    mysqli_query($conn, $mark_read_query);
}

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $current_time = date('Y-m-d H:i:s');
    $has_attachment = 0;
    
    // Always use "Nail Architect" as the subject for salon messages
    $subject = "Nail Architect";
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert message as sent by salon (sender_id = NULL)
        $query = "INSERT INTO messages (user_id, sender_id, subject, content, has_attachment, read_status, created_at) 
                 VALUES ('$user_id', NULL, '$subject', '$content', '$has_attachment', 0, '$current_time')";
        
        if (!mysqli_query($conn, $query)) {
            throw new Exception("Failed to send message: " . mysqli_error($conn));
        }
        
        $message_id = mysqli_insert_id($conn);
        
        // Handle file upload if present
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $upload_dir = '../uploads/messages/';
            
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
                               VALUES ('$message_id', '$file_name', '$file_path', '$file_size', '$file_type')";
                
                if (!mysqli_query($conn, $attach_query)) {
                    throw new Exception("Failed to save attachment: " . mysqli_error($conn));
                }
                
                // Update message to indicate it has an attachment
                $update_query = "UPDATE messages SET has_attachment = 1 WHERE id = '$message_id'";
                if (!mysqli_query($conn, $update_query)) {
                    throw new Exception("Failed to update message: " . mysqli_error($conn));
                }
            } else {
                throw new Exception("Failed to upload file");
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Redirect to refresh the page
        header("Location: admin-messages.php?user_id=$user_id");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nail Architect - Admin Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #F2E9E9;
            padding: 0;
        }
        
        .sidebar {
            width: 250px;
            background-color: #E8D7D0;
            height: 100vh;
            padding: 25px 0;
            position: fixed;
            overflow-y: auto;
            left: 0;
            top: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            z-index: 100;
        }
        
        .logo-container {
            padding: 0 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e0c5b7;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo::after {
            content: "";
            position: absolute;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #dcdcdc;
            right: -8px;
            bottom: -8px;
        }
        
        .admin-title {
            margin-left: 15px;
            font-weight: 600;
            font-size: 18px;
        }
        
        .nav-menu {
            margin-top: 20px;
        }
        
        .menu-section {
            margin-bottom: 10px;
            padding: 0 20px;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            border-left: 4px solid transparent;
        }
        
        .menu-item:hover {
            background-color: #D9BBB0;
        }
        
        .menu-item.active {
            background-color: #D9BBB0;
            border-left-color: #333;
        }
        
        .menu-icon {
            width: 24px;
            margin-right: 10px;
            text-align: center;
            font-size: 16px;
        }
        
        .menu-text {
            font-size: 14px;
            font-weight: 500;
        }
        
        a {
            color: inherit;
            text-decoration: none;
        }
        
        .content-wrapper {
            margin-left: 250px;
            padding: 25px;
            padding-top: 80px;
        }
        
        .top-bar {
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            height: 60px;
            background-color: #E8D7D0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            z-index: 99;
        }
        
        .page-title {
            font-size: 22px;
            font-weight: 600;
        }
        
        .messages-container {
            display: flex;
            gap: 20px;
            height: calc(100vh - 140px);
            margin-top: 20px;
        }
        
        .messages-list {
            width: 320px;
            background-color: white;
            border-radius: 15px;
            overflow-y: auto;
            max-height: 100%;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        
        .messages-list-header {
            padding: 12px 15px;
            background: linear-gradient(to right, rgb(222, 131, 131), rgb(111, 33, 50));
            color: white;
            font-weight: 500;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1;
            border-radius: 15px 15px 0 0;
        }
        
        .messages-count {
            padding: 3px 10px;
            background-color: rgba(255,255,255,0.2);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .messages-list-content {
            flex: 1;
            overflow-y: auto;
        }
        
        .conversation-wrapper {
            flex: 1;
            background-color: white;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        
        .conversation-container {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background-color: #F2E9E9;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .user-item {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .user-item:hover {
            background-color: #f9f2f2;
        }
        
        .user-item.active {
            background-color: #f9f2f2;
        }
        
        .user-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .user-time {
            font-size: 12px;
            color: #777;
        }
        
        .user-email {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .last-message {
            font-size: 12px;
            color: #888;
            display: flex;
            justify-content: space-between;
        }
        
        .unread-badge {
            background-color: #ff0000;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
        }
        
        .conversation-header {
            padding: 12px 15px;
            background: linear-gradient(to right, rgb(222, 131, 131), rgb(111, 33, 50));
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .user-info h3 {
            font-size: 16px;
            margin-bottom: 3px;
            font-weight: 500;
        }
        
        .user-info p {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .header-actions {
            display: flex;
            gap: 8px;
        }
        
        .header-action {
            padding: 6px 12px;
            border-radius: 8px;
            background-color: rgba(255,255,255,0.2);
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            color: white;
            border: none;
        }
        
        .header-action:hover {
            background-color: rgba(255,255,255,0.3);
        }
        
        .chat-message {
        display: flex;
        gap: 8px;
        max-width: 85%;
        margin-bottom: 10px;
        animation: messageIn 0.3s ease-out forwards;
    }
        
        .chat-message.user {
        margin-right: auto;
        flex-direction: row;
    }
        
        .chat-message.salon {
        margin-left: auto;
        flex-direction: row-reverse;
    }
        
        .chat-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #e0c5b7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 500;
        color: #333;
        flex-shrink: 0;
    }
        
        .chat-message.user .chat-avatar {
        background-color: #e0c5b7;
        color: #333;
    }
        
        .chat-message.salon .chat-avatar {
        background-color: #9b59b6;
        color: white;
    }
        
        .chat-bubble {
        background-color: white;
        padding: 10px 12px;
        border-radius: 15px;
        border-top-left-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        font-size: 14px;
        line-height: 1.5;
        word-break: break-word;
    }
        
        .chat-message.user .chat-bubble {
        background-color: white;
        color: #333;
        border-radius: 15px;
        border-top-left-radius: 5px;
    }
        
        .chat-message.salon .chat-bubble {
        background-color: #e6a4a4;
        color: white;
        border-radius: 15px;
        border-top-right-radius: 5px;
        border-top-left-radius: 15px;
    }
        
        .message-time {
            font-size: 11px;
            color: #888;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .chat-message.user .message-time {
            justify-content: flex-end;
            color: rgba(255,255,255,0.8);
        }
        
        .date-separator {
            text-align: center;
            margin: 15px 0;
            position: relative;
        }
        
        .date-separator::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 40%;
            height: 1px;
            background-color: #ddd;
        }
        
        .date-separator::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            width: 40%;
            height: 1px;
            background-color: #ddd;
        }
        
        .date-text {
            background-color: #F2E9E9;
            padding: 0 15px;
            display: inline-block;
            position: relative;
            font-size: 12px;
            color: #888;
        }
        
        .composition-area {
            padding: 12px;
            display: flex;
            gap: 8px;
            border-top: 1px solid #eee;
            align-items: center;
            background-color: white;
        }
        
        .message-form {
            display: flex;
            width: 100%;
            gap: 8px;
            align-items: center;
        }
        
        .attach-button {
            background-color: transparent;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            color: #d98d8d;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .attach-button:hover {
            background-color: #f5f5f5;
        }
        
        .chat-input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #e0c5b7;
            border-radius: 20px;
            outline: none;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        
        .chat-input:focus {
            border-color: #e6a4a4;
        }
        
        .chat-send {
            background: linear-gradient(to right, #d98d8d, #e6a4a4);
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .chat-send:hover {
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            transform: scale(1.05);
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            text-align: center;
            padding: 0 20px;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .empty-state p {
            font-size: 14px;
            max-width: 400px;
            line-height: 1.5;
            opacity: 0.8;
        }
        
        .attachment-preview {
            position: absolute;
            bottom: 100%;
            left: 0;
            right: 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            margin-bottom: 8px;
            padding: 10px;
            display: none;
        }
        
        .attachment-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            background-color: #f5f5f5;
            border-radius: 8px;
        }
        
        .attachment-icon {
            width: 40px;
            height: 40px;
            background-color: #e6a4a4;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .attachment-info {
            flex: 1;
        }
        
        .attachment-name {
            font-size: 14px;
            font-weight: 500;
            color: #333;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .attachment-size {
            font-size: 12px;
            color: #888;
        }
        
        .remove-attachment {
            background-color: transparent;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 5px;
            transition: color 0.2s;
        }
        
        .remove-attachment:hover {
            color: #ff5252;
        }
        
        .message-attachments {
            margin-top: 8px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .message-attachment {
            max-width: 200px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .message-attachment:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .message-attachment img {
            width: 100%;
            display: block;
        }
        
        .file-attachment {
            display: inline-flex;
            align-items: center;
            background-color: rgba(255,255,255,0.9);
            padding: 6px 10px;
            border-radius: 12px;
            font-size: 13px;
            gap: 6px;
            transition: all 0.3s ease;
            color: #333;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .file-attachment:hover {
            background-color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        
        /* Animation for messages */
        @keyframes messageIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Media Queries */
        @media (max-width: 1200px) {
            .messages-container {
                flex-direction: column;
                height: auto;
            }
            
            .messages-list {
                width: 100%;
                max-height: 300px;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                z-index: 1000;
            }
            
            .content-wrapper {
                margin-left: 80px;
            }
            
            .top-bar {
                left: 80px;
            }
            
            .admin-title, .menu-text, .menu-section {
                display: none;
            }
            
            .menu-item {
                justify-content: center;
                padding: 15px 0;
            }
            
            .menu-icon {
                margin-right: 0;
                font-size: 20px;
            }
            
            .logo-container {
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .content-wrapper {
                padding: 15px;
                padding-top: 70px;
            }
            
            .conversation-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .header-actions {
                width: 100%;
            }
            
            .user-info-row {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-spa" style="font-size: 20px; z-index: 1;"></i>
            </div>
            <div class="admin-title">Admin</div>
        </div>
        
       <div class="nav-menu">
    <div class="menu-section">MAIN</div>
    
    <div class="menu-item" onclick="window.location.href='admin-dashboard.php'">
        <div class="menu-icon"><i class="fas fa-tachometer-alt"></i></div>
        <div class="menu-text">Dashboard</div>
    </div>
    
    <div class="menu-item" onclick="window.location.href='admin-appointments.php'">
        <div class="menu-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="menu-text">Appointments</div>
    </div>

    <div class="menu-item" onclick="window.location.href='admin-management.php'">
        <div class="menu-icon"><i class="fas fa-user-shield"></i></div>
        <div class="menu-text">Admin Users</div>
    </div>
    
    <div class="menu-item" onclick="window.location.href='clients.php'">
        <div class="menu-icon"><i class="fas fa-users"></i></div>
        <div class="menu-text">Clients</div>
    </div>
    
    <div class="menu-item active" onclick="window.location.href='admin-messages.php'">
        <div class="menu-icon"><i class="fas fa-envelope"></i></div>
        <div class="menu-text">Messages</div>
    </div>
    
    <div class="menu-section">SYSTEM</div>
    
    <div class="menu-item" onclick="window.location.href='admin-backup.php'">
        <div class="menu-icon"><i class="fas fa-database"></i></div>
        <div class="menu-text">Backup & Restore</div>
    </div>
    
    <div class="menu-item" onclick="window.location.href='logout.php'">
        <div class="menu-icon"><i class="fas fa-sign-out-alt"></i></div>
        <div class="menu-text">Logout</div>
    </div>
</div>
    </div>
    
    <div class="top-bar">
        <div class="page-title">Client Messages</div>
    </div>
    
    <div class="content-wrapper">
        <div class="messages-container">
            <div class="messages-list">
                <div class="messages-list-header">
                    <span>Client Conversations</span>
                    <?php if (mysqli_num_rows($messages_result) > 0): ?>
                        <span class="messages-count"><?php echo mysqli_num_rows($messages_result); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="messages-list-content">
                    <?php if (mysqli_num_rows($messages_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($messages_result)): ?>
                            <?php
                            $user_full_name = $row['first_name'] . ' ' . $row['last_name'];
                            $message_date = new DateTime($row['last_message']);
                            $now = new DateTime();
                            $interval = $message_date->diff($now);
                            
                            if ($interval->days == 0) {
                                $date_text = 'Today';
                            } elseif ($interval->days == 1) {
                                $date_text = 'Yesterday';
                            } else {
                                $date_text = $message_date->format('M j');
                            }
                            ?>
                            <a href="?user_id=<?php echo $row['user_id']; ?>">
                                <div class="user-item <?php echo (isset($_GET['user_id']) && $_GET['user_id'] == $row['user_id']) ? 'active' : ''; ?>">
                                    <div class="user-info-row">
                                        <div class="user-name">
                                            <?php echo $user_full_name; ?>
                                            <?php if ($row['unread_count'] > 0): ?>
                                                <span class="unread-badge"><?php echo $row['unread_count']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="user-time"><?php echo $date_text; ?></div>
                                    </div>
                                    <div class="user-email"><?php echo $row['email']; ?></div>
                                    <div class="last-message">
                                        <span><i class="fas fa-comment-dots" style="margin-right: 5px;"></i> <?php echo $row['message_count']; ?> messages</span>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state" style="height: 200px;">
                            <p>No message threads found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="conversation-wrapper">
                <?php if (!empty($conversation)): ?>
                    <div class="conversation-header">
                        <div class="user-info">
                            <h3><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h3>
                            <p><?php echo $user['email']; ?></p>
                        </div>
                        
                        <div class="header-actions">
                            <!-- <a href="admin-client-details.php?id=<?php echo $user['id']; ?>" class="header-action">
                                <!-- <i class="fas fa-user"></i> View Profile -->
                            <!-- </a> --> 
                            
                            <!-- <?php if (isset($user['phone']) && !empty($user['phone'])): ?>
                                <a href="tel:<?php echo $user['phone']; ?>" class="header-action">
                                    <i class="fas fa-phone"></i> Call
                                </a>
                            <?php endif; ?> -->
                        </div>
                    </div>
                    
                    <div class="conversation-container">
                        <?php
                        $current_date = '';
                        foreach ($conversation as $message):
                            $message_date = new DateTime($message['created_at']);
                            $date_string = $message_date->format('Y-m-d');
                            
                            if ($date_string != $current_date):
                                $current_date = $date_string;
                                
                                // Format date for display
                                $now = new DateTime();
                                $yesterday = new DateTime('yesterday');
                                
                                if ($message_date->format('Y-m-d') == $now->format('Y-m-d')) {
                                    $display_date = 'Today';
                                } elseif ($message_date->format('Y-m-d') == $yesterday->format('Y-m-d')) {
                                    $display_date = 'Yesterday';
                                } else {
                                    $display_date = $message_date->format('F j, Y');
                                }
                        ?>
                            <div class="date-separator">
                                <span class="date-text"><?php echo $display_date; ?></span>
                            </div>
                        <?php endif; ?>
                        
                            <div class="chat-message <?php echo $message['sender_type']; ?>">
                                <div class="chat-avatar">
                                    <?php if ($message['sender_type'] == 'salon'): ?>
                                        NA
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="chat-content">
                                    <div class="chat-bubble"><?php echo nl2br($message['content']); ?></div>
                                    <div class="message-time">
                                        <i class="far fa-clock"></i> <?php echo $message_date->format('g:i A'); ?>
                                    </div>
                                    
                                    <?php if (!empty($message['attachments'])): ?>
                                        <div class="message-attachments">
                                            <?php foreach ($message['attachments'] as $attachment): ?>
                                                <?php
                                                $is_image = in_array($attachment['file_type'], ['image/jpeg', 'image/png', 'image/gif']);
                                                ?>
                                                
                                                <?php if ($is_image): ?>
                                                    <div class="message-attachment">
                                                        <a href="<?php echo $attachment['file_path']; ?>" target="_blank">
                                                            <img src="<?php echo $attachment['file_path']; ?>" alt="<?php echo $attachment['file_name']; ?>" loading="lazy">
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <a href="<?php echo $attachment['file_path']; ?>" target="_blank" class="file-attachment">
                                                        <i class="fas fa-paperclip"></i>
                                                        <span><?php echo $attachment['file_name']; ?></span>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="composition-area">
                        <form action="" method="POST" enctype="multipart/form-data" class="message-form">
                            <input type="hidden" name="action" value="send_message">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            
                            <button type="button" class="attach-button" id="attach-btn">
                                <i class="fas fa-paperclip"></i>
                            </button>
                            <input type="file" name="attachment" id="attachment" style="display: none;">
                            
                            <input type="text" name="content" placeholder="Type your message..." required class="chat-input">
                            
                            <button type="submit" class="chat-send">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                            
                            <div class="attachment-preview" id="attachment-preview"></div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="far fa-comments"></i></div>
                        <h3>No conversation selected</h3>
                        <p>Select a client from the list to view and respond to their messages.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Scroll to bottom of conversation when loading a conversation
        const conversationContainer = document.querySelector('.conversation-container');
        if (conversationContainer) {
            conversationContainer.scrollTop = conversationContainer.scrollHeight;
        }
        
        // File upload functionality
        const fileInput = document.getElementById('attachment');
        const attachBtn = document.getElementById('attach-btn');
        const preview = document.getElementById('attachment-preview');
        const messageForm = document.querySelector('.message-form');
        
        // Click on paperclip to open file selector
        if (attachBtn) {
            attachBtn.addEventListener('click', function() {
                fileInput.click();
            });
        }
        
        // Handle file selection
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    showAttachmentPreview(file);
                }
            });
        }
        
        function showAttachmentPreview(file) {
            const isImage = file.type.startsWith('image/');
            
            preview.innerHTML = `
                <div class="attachment-item">
                    <div class="attachment-icon">
                        ${isImage ? '<i class="fas fa-image"></i>' : '<i class="fas fa-file"></i>'}
                    </div>
                    <div class="attachment-info">
                        <div class="attachment-name">${file.name}</div>
                        <div class="attachment-size">${formatFileSize(file.size)}</div>
                    </div>
                    <button type="button" class="remove-attachment" onclick="removeAttachment()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            preview.style.display = 'block';
            
            // If it's an image, show a thumbnail
            if (isImage) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const icon = preview.querySelector('.attachment-icon');
                    icon.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">`;
                };
                reader.readAsDataURL(file);
            }
        }
        
        window.removeAttachment = function() {
            fileInput.value = '';
            preview.innerHTML = '';
            preview.style.display = 'none';
        };
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Enter key to send message (shift+enter for new line)
        const messageInput = document.querySelector('.chat-input');
        if (messageInput) {
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const form = this.closest('form');
                    if (form && this.value.trim().length > 0) {
                        form.submit();
                    }
                }
            });
        }
        
        // Auto-resize textarea based on content
        if (messageInput) {
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 150) + 'px';
            });
        }
        
        // Add animation to new messages
        const messages = document.querySelectorAll('.chat-message');
        messages.forEach((message, index) => {
            message.style.animationDelay = `${index * 0.05}s`;
        });
        
        // Show typing indicator when typing (this would need backend support)
        let typingTimer;
        let isTyping = false;
        
        if (messageInput) {
            messageInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                
                if (!isTyping) {
                    isTyping = true;
                    // Send typing status to server
                    // This would require implementing backend support
                }
                
                typingTimer = setTimeout(() => {
                    isTyping = false;
                    // Send stop typing status to server
                }, 1000);
            });
        }
    });
    </script>
</body>
</html>