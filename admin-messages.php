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
            background-color: #E8D7D0;
            border-radius: 15px;
            overflow-y: auto;
            max-height: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        
        .messages-list-header {
            padding: 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            font-weight: 600;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background-color: #E8D7D0;
            z-index: 1;
        }
        
        .messages-count {
            padding: 3px 10px;
            background-color: rgba(0,0,0,0.1);
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
            background-color: #E8D7D0;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .conversation-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background-color: rgba(255,255,255,0.1);
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
            background-color: #D9BBB0;
        }
        
        .user-item.active {
            background-color: #D9BBB0;
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
            background-color: #FF5252;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
        }
        
        .conversation-header {
            padding: 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #E8D7D0;
        }
        
        .user-info h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .user-info p {
            font-size: 14px;
            color: #666;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .header-action {
            padding: 8px 16px;
            border-radius: 8px;
            background-color: #D9BBB0;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .header-action:hover {
            background-color: #ae9389;
        }
        
        .message-bubble {
            max-width: 70%;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 18px;
            position: relative;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        
       .message-bubble.salon {
    background-color: #D9BBB0;
    border-bottom-right-radius: 5px;
    margin-left: auto;
    margin-right: 0;
}
        
       .message-bubble.user {
    background-color: #e0c5b7;
    border-bottom-left-radius: 5px;
    margin-right: auto;
    margin-left: 0;
}
.message-bubble.user .message-content {
    text-align: left;
}

.message-bubble.salon .message-content {
    text-align: right;
}

.message-bubble.salon .message-sender {
    text-align: right;
}

.message-bubble.user .message-sender {
    text-align: left;
}
        
        .message-sender {
            font-weight: 600;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }
        
        .message-content {
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }
        
        .message-time {
            font-size: 11px;
            color: #777;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .message-date {
            text-align: center;
            margin: 20px 0;
            font-size: 13px;
            color: #666;
            position: relative;
            font-weight: 500;
        }
        
        .message-date::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background-color: rgba(0,0,0,0.1);
        }
        
        .message-date::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background-color: rgba(0,0,0,0.1);
        }
        
        .message-date span {
            background-color: #E8D7D0;
            padding: 0 15px;
            position: relative;
            z-index: 1;
            opacity: 0.9;
        }
        
        .composition-area {
            padding: 20px;
            border-top: 1px solid rgba(0,0,0,0.1);
            background-color: #E8D7D0;
        }
        
        .message-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .composition-row {
            display: flex;
            gap: 10px;
        }
        
        .composition-row textarea {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 15px;
            font-size: 14px;
            background-color: white;
            min-height: 100px;
            resize: vertical;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .composition-row textarea:focus {
            outline: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .file-input-container {
            position: relative;
        }
        
        .file-input-label {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: #D9BBB0;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 14px;
        }
        
        .file-input-label:hover {
            background-color: #ae9389;
        }
        
        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .send-button {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            background-color: #D9BBB0;
            color: #333;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .send-button:hover {
            background-color: #ae9389;
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
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .attachment-item {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .attachment-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .file-icon {
            font-size: 24px;
            color: #777;
        }
        
        .attachment-name {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0,0,0,0.5);
            color: white;
            font-size: 9px;
            padding: 3px 5px;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }
        
        .message-attachments {
            display: flex;
            gap: 10px;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        
        .message-attachment {
            max-width: 200px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .message-attachment:hover {
            transform: scale(1.03);
        }
        
        .message-attachment img {
            width: 100%;
            display: block;
        }
        
        .file-attachment {
            display: flex;
            align-items: center;
            background-color: rgba(0,0,0,0.05);
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .file-attachment:hover {
            background-color: rgba(0,0,0,0.1);
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
            
            .composition-row {
                flex-direction: column;
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
            
            <a href="admin-dashboard.php" class="menu-item">
                <div class="menu-icon"><i class="fas fa-tachometer-alt"></i></div>
                <div class="menu-text">Dashboard</div>
            </a>
            
            <a href="admin-appointments.php" class="menu-item">
                <div class="menu-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="menu-text">Appointments</div>
            </a>
            
            <a href="admin-clients.php" class="menu-item">
                <div class="menu-icon"><i class="fas fa-users"></i></div>
                <div class="menu-text">Clients</div>
            </a>
            
            <a href="admin-messages.php" class="menu-item active">
                <div class="menu-icon"><i class="fas fa-envelope"></i></div>
                <div class="menu-text">Messages</div>
            </a>
            
            <div class="menu-section">SYSTEM</div>
            
            <a href="admin-backup.php" class="menu-item">
                <div class="menu-icon"><i class="fas fa-database"></i></div>
                <div class="menu-text">Backup & Restore</div>
            </a>
            
            <a href="logout.php" class="menu-item">
                <div class="menu-icon"><i class="fas fa-sign-out-alt"></i></div>
                <div class="menu-text">Logout</div>
            </a>
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
                            <a href="admin-client-details.php?id=<?php echo $user['id']; ?>" class="header-action">
                                <i class="fas fa-user"></i> View Profile
                            </a>
                            
                            <?php if (isset($user['phone']) && !empty($user['phone'])): ?>
                                <a href="tel:<?php echo $user['phone']; ?>" class="header-action">
                                    <i class="fas fa-phone"></i> Call
                                </a>
                            <?php endif; ?>
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
                            <div class="message-date">
                                <span><?php echo $display_date; ?></span>
                            </div>
                        <?php endif; ?>
                        
                            <div class="message-bubble <?php echo $message['sender_type']; ?>">
                                <div class="message-sender">
                                    <?php if ($message['sender_type'] == 'salon'): ?>
                                        Nail Architect
                                    <?php else: ?>
                                        <?php echo $user['first_name'] . ' ' . $user['last_name']; ?>
                                    <?php endif; ?>
                                    <div class="message-time">
                                        <i class="far fa-clock"></i> <?php echo $message_date->format('g:i A'); ?>
                                    </div>
                                </div>
                                <div class="message-content"><?php echo nl2br($message['content']); ?></div>
                                
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
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="composition-area">
                        <form action="" method="POST" enctype="multipart/form-data" class="message-form">
                            <input type="hidden" name="action" value="send_message">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            
                            <div class="composition-row">
                                <textarea name="content" placeholder="Type your message here..." required></textarea>
                            </div>
                            
                            <div class="composition-row" style="justify-content: space-between;">
                                <div class="file-input-container">
                                    <label for="attachment" class="file-input-label">
                                        <i class="fas fa-paperclip"></i> Attach File
                                    </label>
                                    <input type="file" name="attachment" id="attachment" class="file-input">
                                </div>
                                
                                <button type="submit" class="send-button">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </div>
                            
                            <div id="attachment-preview-container"></div>
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
        
        // File upload preview functionality
        const fileInput = document.querySelector('input[type="file"]');
        const previewContainer = document.getElementById('attachment-preview-container');
        
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                // Clear previous preview
                previewContainer.innerHTML = '';
                
                if (this.files.length > 0) {
                    // Create preview container
                    const attachmentPreview = document.createElement('div');
                    attachmentPreview.className = 'attachment-preview';
                    
                    const file = this.files[0];
                    const isImage = file.type.startsWith('image/');
                    
                    const attachmentItem = document.createElement('div');
                    attachmentItem.className = 'attachment-item';
                    
                    if (isImage) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            attachmentItem.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        const fileIcon = document.createElement('div');
                        fileIcon.className = 'file-icon';
                        fileIcon.innerHTML = '<i class="fas fa-file"></i>';
                        attachmentItem.appendChild(fileIcon);
                    }
                    
                    const fileName = document.createElement('div');
                    fileName.className = 'attachment-name';
                    fileName.textContent = file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name;
                    attachmentItem.appendChild(fileName);
                    
                    // Add remove button
                    const removeButton = document.createElement('div');
                    removeButton.className = 'remove-attachment';
                    removeButton.innerHTML = '<i class="fas fa-times"></i>';
                    removeButton.style.position = 'absolute';
                    removeButton.style.top = '5px';
                    removeButton.style.right = '5px';
                    removeButton.style.backgroundColor = 'rgba(0,0,0,0.5)';
                    removeButton.style.color = 'white';
                    removeButton.style.width = '20px';
                    removeButton.style.height = '20px';
                    removeButton.style.borderRadius = '50%';
                    removeButton.style.display = 'flex';
                    removeButton.style.alignItems = 'center';
                    removeButton.style.justifyContent = 'center';
                    removeButton.style.cursor = 'pointer';
                    removeButton.style.fontSize = '10px';
                    
                    removeButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        fileInput.value = '';
                        previewContainer.innerHTML = '';
                    });
                    
                    attachmentItem.appendChild(removeButton);
                    attachmentPreview.appendChild(attachmentItem);
                    previewContainer.appendChild(attachmentPreview);
                }
            });
        }
        
        // Keyboard shortcut for sending messages (Ctrl+Enter)
        const messageTextarea = document.querySelector('textarea[name="content"]');
        if (messageTextarea) {
            messageTextarea.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    e.preventDefault();
                    const form = this.closest('form');
                    if (form && this.value.trim().length > 0) {
                        form.submit();
                    }
                }
            });
        }
    });
    </script>
</body>
</html>