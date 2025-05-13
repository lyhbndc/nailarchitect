<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get user data from database for the logged in user
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    // Redirect to login if user not found
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get first letter of first name for avatar
$first_letter = substr($user['first_name'], 0, 1);

// Fetch user's bookings
$upcoming_bookings = [];
$past_bookings = [];

// Get upcoming bookings (pending and confirmed)
$upcoming_query = "SELECT * FROM bookings WHERE user_id = ? AND (status = 'pending' OR status = 'confirmed') AND date >= CURDATE() ORDER BY date ASC, time ASC";
$upcoming_stmt = $conn->prepare($upcoming_query);
$upcoming_stmt->bind_param("i", $user_id);
$upcoming_stmt->execute();
$upcoming_result = $upcoming_stmt->get_result();

while ($row = $upcoming_result->fetch_assoc()) {
    $upcoming_bookings[] = $row;
}

// Get past bookings (completed or cancelled or past date)
$past_query = "SELECT * FROM bookings WHERE user_id = ? AND (status = 'completed' OR status = 'cancelled' OR date < CURDATE()) ORDER BY date DESC, time DESC";
$past_stmt = $conn->prepare($past_query);
$past_stmt->bind_param("i", $user_id);
$past_stmt->execute();
$past_result = $past_stmt->get_result();

while ($row = $past_result->fetch_assoc()) {
    $past_bookings[] = $row;
}

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="bg-gradient.css">
    <link rel="icon" type="image/png" href="Assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Nail Architect - Members Lounge</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #f2e9e9;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            max-width: 1500px;
            width: 100%;
            flex: 1;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
        }
        
        .page-title {
            font-size: 24px;
            margin-bottom: 10px;
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .page-subtitle {
            font-size: 16px;
            margin-bottom: 25px;
            color: #666;
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .member-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            animation: fadeIn 0.7s ease-out forwards;
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .profile-card {
  background-color: rgb(245, 207, 207);
  border-radius: 15px;
  padding: 25px;
  text-align: center;
  border: 1px solid rgba(235, 184, 184, 0.3);
  box-shadow: 
    0 4px 16px rgba(0, 0, 0, 0.1),
    0 2px 8px rgba(0, 0, 0, 0.05),
    inset 0 1px 2px rgba(255, 255, 255, 0.3);
}
        
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
        }
        
        .profile-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .profile-email {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .profile-phone {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .action-button {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            display: inline-block;
            color: black;
            text-decoration: none;
        }
        
        .action-button:hover {
            background: linear-gradient(to right, #d98d8d, #ce7878);
        }
        
        .menu-card {
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            padding: 20px;
            background-color: rgb(245, 207, 207);
  border: 1px solid rgba(235, 184, 184, 0.3);
  box-shadow: 
    0 4px 16px rgba(0, 0, 0, 0.1),
    0 2px 8px rgba(0, 0, 0, 0.05),
    inset 0 1px 2px rgba(255, 255, 255, 0.3);
        }
        
        .menu-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .menu-item {
            padding: 12px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            
        }
        
        .menu-item:hover {
            background-color: rgb(236, 201, 201);
        }
        
        .menu-item.active {
            background-color: rgb(215, 165, 165);
            font-weight: bold;
        }
        
        .menu-icon {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            padding: 30px;
            border: 1px solid rgba(235, 184, 184, 0.3);
  box-shadow: 
    0 4px 16px rgba(0, 0, 0, 0.1),
    0 2px 8px rgba(0, 0, 0, 0.05),
    inset 0 1px 2px rgba(255, 255, 255, 0.3);
        }
        
        .appointments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgb(142, 130, 130); ;
        }
        
        .appointments-count {
            font-size: 14px;
            color: #666;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 0;
        }
        
        /* Fade effect at bottom when scrollable */
        .appointments-wrapper {
            position: relative;
            height: 100%;
            overflow: hidden;
        }
        
        .appointments-wrapper.has-scroll::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: linear-gradient(to bottom, transparent, #e8d7d0);
            pointer-events: none;
        }
        
        .appointments-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-height: 600px; /* Add maximum height */
            overflow-y: auto; /* Enable vertical scrolling */
            padding-right: 10px; /* Add padding for scrollbar */
        }
        
        /* Custom scrollbar styling */
        .appointments-list::-webkit-scrollbar {
            width: 8px;
        }
        
        .appointments-list::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 10px;
        }
        
        .appointments-list::-webkit-scrollbar-thumb {
            background: #d9bbb0;
            border-radius: 10px;
        }
        
        .appointments-list::-webkit-scrollbar-thumb:hover {
            background: #ae9389;
        }
        
        .appointment-card {
            background-color: #f2e9e9;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .appointment-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .appointment-service {
            font-weight: bold;
            font-size: 16px;
        }
        
        .appointment-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-confirmed {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .status-pending {
            background-color: #fff9c4;
            color: #f57f17;
        }
        
        .status-completed {
            background-color: #e0e0e0;
            color: #616161;
        }
        
        .status-cancelled {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .appointment-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .detail-label {
            font-size: 12px;
            color: #666;
        }
        
        .detail-value {
            font-size: 14px;
        }
        
        .appointment-actions {
            display: flex;
            gap: 10px;
        }
        
        .tab-container {
            margin-bottom: 30px;
        }
        
        .tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            border-radius: 8px 8px 0 0;
            background-color: rgb(216, 189, 189);
            cursor: pointer;
            transition: all 0.3s ease;
            color: #fff;
        }
        
        .tab.active {
            background-color: rgb(215, 165, 165);
            font-weight: bold;
            color: #333;
        }
        
        .tab-content {
            display: none;
            height: calc(100% - 60px); /* Adjust based on tab height */
            overflow: hidden;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .contact-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-group input[type="checkbox"] {
    width: auto;
    margin-right: 8px;
    vertical-align: middle;
}

.form-group label[for="update-past-records"] {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
}

.form-group label[for="update-past-records"] input[type="checkbox"] {
    margin-top: 2px;
    flex-shrink: 0;
}

.form-group p {
    margin-left: 26px; /* Indent to align with checkbox text */
}
        
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: #ffffff;
            font-family: Poppins;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .submit-button {
            padding: 12px 24px;
            background-color: #c0c0c0;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            margin-top: 20px;
            font-weight: bold;
        }
        
        .submit-button:hover {
            background-color: #b0b0b0;
            transform: translateY(-2px);
        }
        
        .no-appointments {
            text-align: center;
            padding: 40px 0;
            color: #666;
        }
        
        .no-appointments-icon {
            font-size: 36px;
            margin-bottom: 20px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            position: relative;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
        }
        
        .close-modal:hover {
            background: linear-gradient(to right, #d98d8d, #ce7878);
        }
        
        .date-picker, .time-picker {
            margin-bottom: 20px;
        }
        
        .modal-buttons {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-top: 30px;
        }
        
        .modal-button {
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            cursor: pointer;
            flex: 1;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .confirm-button {
            background-color: #d9bbb0;
        }
        
        .confirm-button:hover {
            background-color: #ae9389;
        }
        
        .cancel-button {
            background-color: #d9bbb0;
        }
        
        .cancel-button:hover {
            background-color: #ae9389;
        }
        
        /* Responsive styles */
        @media (max-width: 1024px) {
            .member-content {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                grid-template-columns: 1fr;
            }
            
            .appointment-details {
                grid-template-columns: 1fr;
            }
            
            .contact-form {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
        }
        .messages-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .message-list {
        max-height: 400px;
        overflow-y: auto;
        background-color: #f2e9e9;
        border-radius: 12px;
        padding: 15px;
    }
    
    .message-item {
        background-color: #ffffff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .message-item:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .message-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .message-subject {
        font-weight: bold;
        font-size: 16px;
    }
    
    .message-date {
        color: #666;
        font-size: 12px;
    }
    
    .message-body {
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 10px;
    }
    
    .message-sender {
        font-size: 12px;
        color: #888;
        text-align: right;
    }
    
    .message-compose {
        background-color: #f2e9e9;
        border-radius: 12px;
        padding: 20px;
    }
    
    .loading-messages {
        text-align: center;
        padding: 30px;
        color: #888;
    }
    
    .no-messages {
        text-align: center;
        padding: 40px 0;
        color: #666;
    }
    
    .message-item.from-salon {
        background-color: #e0c5b7;
    }
    
    .message-item.unread {
        border-left: 3px solid #ae9389;
    }
    
    .message-role {
        display: inline-block;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 5px;
        background-color: #f0f0f0;
    }
    
    .message-role.salon {
        background-color: #d9bbb0;
    }

    .message-attachments {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.attachment-preview {
    width: 150px;
    border-radius: 8px;
    overflow: hidden;
    background-color: #f0f0f0;
}

.attachment-image {
    width: 100%;
    height: 100px;
    object-fit: cover;
    display: block;
}

.attachment-name {
    padding: 5px;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
}

.attachment-file {
    background-color: #f0f0f0;
    border-radius: 8px;
    padding: 10px;
}

.attachment-link {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #333;
    text-decoration: none;
}

.attachment-icon {
    font-size: 16px;
}

.attachment-note {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

input[type="file"] {
    padding: 8px;
    background-color: #fff;
}

/* Conversation styles */
.conversation-container {
    display: flex;
    flex-direction: column;
    height: 100%;
    max-height: 600px;
}

.message-list {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    background-color: #f2e9e9;
    border-radius: 12px 12px 0 0;
    display: flex;
    flex-direction: column;
}

.message-item {
    max-width: 80%;
    margin-bottom: 10px;
    padding: 10px 15px;
    border-radius: 18px;
    position: relative;
    word-wrap: break-word;
}

.message-item.from-salon {
    align-self: flex-start;
    background-color: #e0c5b7;
    border-bottom-left-radius: 5px;
}

.message-item.from-user {
    align-self: flex-end;
    background-color: #d9bbb0;
    border-bottom-right-radius: 5px;
    color: #333;
}

.message-sender {
    font-size: 12px;
    margin-bottom: 5px;
    font-weight: bold;
}

.message-content {
    font-size: 14px;
    line-height: 1.5;
}

.message-time {
    font-size: 10px;
    color: #666;
    text-align: right;
    margin-top: 5px;
}

.message-subject {
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 5px;
    padding-bottom: 5px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.message-compose {
    background-color: #f2e9e9;
    border-radius: 0 0 12px 12px;
    padding: 15px;
    border-top: 1px solid #e0c5b7;
}

.message-input-container {
    display: flex;
    position: relative;
}

.message-input-container textarea {
    flex: 1;
    min-height: 50px;
    max-height: 150px;
    padding-right: 80px;
    border-radius: 20px;
    resize: none;
}

.message-actions {
    position: absolute;
    right: 10px;
    bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.attachment-label {
    cursor: pointer;
    font-size: 20px;
    color: #ae9389;
}

.send-button {
    background-color: #ae9389;
    color: white;
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.send-button i {
    font-size: 16px;
}

.attachment-preview-container {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.attachment-preview {
    position: relative;
    width: 100px;
    height: 100px;
    background-color: #f0f0f0;
    border-radius: 8px;
    overflow: hidden;
}

.attachment-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.attachment-preview .remove-attachment {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: rgba(0,0,0,0.5);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
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
    background-color: #f2e9e9;
    padding: 0 15px;
    display: inline-block;
    position: relative;
    font-size: 12px;
    color: #888;
}

.message-attachments {
    margin-top: 8px;
}

.attachment-file {
    background-color: rgba(255,255,255,0.3);
    padding: 5px 10px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
}

.attachment-name {
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.loading-messages {
    text-align: center;
    padding: 20px;
    color: #888;
}

.no-messages {
    text-align: center;
    padding: 30px 0;
    color: #666;
}

/* New styles for better profile edit modal */
.profile-edit-modal .modal-content {
    max-width: 600px;
}

.profile-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.profile-form .form-group.full-width {
    grid-column: span 2;
}

.profile-modal-buttons {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    grid-column: span 2;
}

.profile-modal-buttons .modal-button {
    flex: 1;
    padding: 12px 24px;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
}

.save-profile-btn {
    background: linear-gradient(to right, #e6a4a4, #d98d8d);
    color: white;
}

.save-profile-btn:hover {
    background: linear-gradient(to right, #d98d8d, #ce7878);
}

.cancel-profile-btn {
    background: linear-gradient(to right, #e6a4a4, #d98d8d);
}

.cancel-profile-btn:hover {
    background: linear-gradient(to right, #d98d8d, #ce7878);
}

.error-message, .success-message {
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
}

.error-message {
    background-color: #ffcdd2;
    color: #c62828;
}

.success-message {
    background-color: #c8e6c9;
    color: #2e7d32;
}
    </style>
</head>
<body>
<div class="gradient-overlay"></div>
    <div class="background-pattern"></div>
    <div class="swirl-pattern"></div>
    <div class="polish-drips"></div>
    <div class="container">
       <header>
            <div class="logo-container">
                <div class="logo">
                    <a href="index.php">
                        <img src="Assets/logo.png" alt="Nail Architect Logo">
                    </a>
                </div>
            </div>
            <div class="nav-links">
                <div class="nav-link">Services</div>
                <div class="book-now">Book Now</div>
                <div class="user-initial"><?php echo $first_letter; ?></div>
            </div>
        </header>
        
        <div class="page-title">Welcome, <?php echo $user['first_name']; ?>!</div>
        <div class="page-subtitle">Manage your appointments and account information</div>
        
        <div class="member-content">
            <div class="sidebar">
                <div class="profile-card">
                    <div class="avatar"><?php echo $first_letter; ?></div>
                    <div class="profile-name"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></div>
                    <div class="profile-email"><?php echo $user['email']; ?></div>
                    <div class="profile-phone"><?php echo $user['phone']; ?></div>
                    <div class="action-button" id="edit-profile-btn">Edit Profile</div>
                </div>
                
                <div class="menu-card">
                    <div class="menu-title">Account Menu</div>
                    
                    <div class="menu-item active" id="appointments-menu">
                        <div class="menu-icon">📅</div>
                        My Appointments
                    </div>
                <div class="menu-item" id="messages-menu">
                <div class="menu-icon">💬</div>
                Messages
            </div>
                    
                    <div class="menu-item" id="logout-menu">
                        <div class="menu-icon">↩️</div>
                        Logout
                    </div>
                </div>
            </div>
            
            <div class="main-content">
                <div class="tab-container">
                    <div class="tabs">
                        <div class="tab active" data-tab="upcoming">Upcoming</div>
                        <div class="tab" data-tab="past">Past</div>
                    </div>
                    
                    <div class="tab-content active" id="upcoming-tab">
                        <div class="appointments-header">
                            <h2 class="section-title">Upcoming Appointments</h2>
                            <span class="appointments-count"><?php echo count($upcoming_bookings); ?> appointment<?php echo count($upcoming_bookings) !== 1 ? 's' : ''; ?></span>
                        </div>
                        
                        <div class="appointments-wrapper <?php echo count($upcoming_bookings) > 2 ? 'has-scroll' : ''; ?>">
                            <div class="appointments-list" id="upcoming-appointments">
                                <?php if (empty($upcoming_bookings)): ?>
                                    <div class="no-appointments">
                                        <div class="no-appointments-icon">📅</div>
                                        <p>You don't have any upcoming appointments.</p>
                                        <a href="booking.php" class="action-button">Book Appointment</a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($upcoming_bookings as $booking): ?>
                                        <div class="appointment-card" data-id="appointment-<?php echo $booking['id']; ?>">
                                            <div class="appointment-card-header">
                                                <div class="appointment-service">
                                                    <?php 
                                                    // Format service name
                                                    $service_name = ucfirst(str_replace('-', ' ', $booking['service']));
                                                    echo $service_name; 
                                                    ?>
                                                </div>
                                                <div class="appointment-status status-<?php echo strtolower($booking['status']); ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </div>
                                            </div>
                                            
                                            <div class="appointment-details">
                                                <div class="detail-item">
                                                    <div class="detail-label">Date</div>
                                                    <div class="detail-value"><?php echo date('F j, Y', strtotime($booking['date'])); ?></div>
                                                </div>
                                                
                                                <div class="detail-item">
                                                    <div class="detail-label">Time</div>
                                                    <div class="detail-value">
                                                        <?php 
                                                        // Format time (12-hour format)
                                                        echo date('g:i A', strtotime($booking['time'])); 
                                                        ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="detail-item">
                                                    <div class="detail-label">Duration</div>
                                                    <div class="detail-value"><?php echo $booking['duration']; ?> minutes</div>
                                                </div>
                                                
                                                <div class="detail-item">
                                                    <div class="detail-label">Technician</div>
                                                    <div class="detail-value"><?php echo $booking['technician']; ?></div>
                                                </div>
                                                
                                                <div class="detail-item">
                                                    <div class="detail-label">Price</div>
                                                    <div class="detail-value">₱<?php echo number_format($booking['price'], 2); ?></div>
                                                </div>
                                                
                                                <div class="detail-item">
        <div class="detail-label">Reference</div>
        <div class="detail-value">#<?php echo $booking['reference_id']; ?></div>
    </div>
                                            </div>
                                            
                                            <?php if ($booking['status'] != 'cancelled'): ?>
                                            <div class="appointment-actions">
                                                <div class="action-button reschedule-btn" data-id="<?php echo $booking['id']; ?>">Reschedule</div>
                                                <div class="action-button cancel-btn" data-id="<?php echo $booking['id']; ?>">Cancel</div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-content" id="past-tab">
                        <div class="appointments-header">
                            <h2 class="section-title">Past Appointments</h2>
                            <span class="appointments-count"><?php echo count($past_bookings); ?> appointment<?php echo count($past_bookings) !== 1 ? 's' : ''; ?></span>
                        </div>
                        
                        <div class="appointments-wrapper <?php echo count($past_bookings) > 2 ? 'has-scroll' : ''; ?>">
                            <div class="appointments-list" id="past-appointments">
                                <?php if (empty($past_bookings)): ?>
                                    <div class="no-appointments">
                                        <div class="no-appointments-icon">📅</div>
                                        <p>You don't have any past appointments.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($past_bookings as $booking): ?>
                                        <div class="appointment-card" data-id="appointment-<?php echo $booking['id']; ?>">
                                            <div class="appointment-header">
                                                <div class="appointment-service">
                                                    <?php 
                                                    // Format service name
                                                    $service_name = ucfirst(str_replace('-', ' ', $booking['service']));
                                                    echo $service_name; 
                                                    ?>
                                                </div>
                                                <div class="appointment-status status-<?php echo strtolower($booking['status']); ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </div>
                                            </div>
                                            
                                            <div class="appointment-details">
                                                <div class="detail-item">
                                                    <div class="detail-label">Date</div>
                                                    <div class="detail-value"><?php echo date('F j, Y', strtotime($booking['date'])); ?></div>
                                                </div>
                                                
                                                <div class="detail-item">
                                                    <div class="detail-label">Time</div>
                                                    <div class="detail-value">
                                                        <?php 
                                                        // Format time (12-hour format)
                                                        echo date('g:i A', strtotime($booking['time'])); 
                                                        ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="detail-item">
                                                    <div class="detail-label">Duration</div>
                                                    <div class="detail-value"><?php echo $booking['duration']; ?> minutes</div>
                                                </div>
                                                
                                                <div class="detail-item">
                                                    <div class="detail-label">Technician</div>
                                                    <div class="detail-value"><?php echo $booking['technician']; ?></div>
                                                </div>
                                                
                                                <div class="detail-item">
                                                    <div class="detail-label">Price</div>
                                                    <div class="detail-value">₱<?php echo number_format($booking['price'], 2); ?></div>
                                                </div>
                                                
                                                <div class="detail-item">
        <div class="detail-label">Reference</div>
        <div class="detail-value">#NAI-<?php echo $booking['reference_id']; ?></div>
    </div>
                                            </div>
                                            
                                            <?php if ($booking['status'] == 'completed'): ?>
                                            <div class="appointment-actions">
                                                <div class="action-button book-again-btn" data-service="<?php echo $booking['service']; ?>">Book Again</div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" id="messages-tab">
    <div class="section-title">Messages</div>
    
    <div class="conversation-container">
        <div class="message-list" id="message-list">
            <!-- Messages will be loaded here dynamically -->
            <div class="loading-messages">Loading your conversation...</div>
        </div>
        
        <div class="message-compose">
            <form id="message-form" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" id="message-subject" name="subject" placeholder="Subject (required for new conversations)" required>
                </div>
                
                <div class="form-group message-input-container">
                    <textarea id="message-content" name="content" placeholder="Type your message here..." required></textarea>
                    <div class="message-actions">
                        <label for="message-attachment" class="attachment-label">
                            <i class="fa fa-paperclip"></i>
                        </label>
                        <input type="file" id="message-attachment" name="attachment" style="display: none;">
                        <button type="submit" class="send-button" id="send-message">
                            <i class="fa fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
                <p class="attachment-note">You can attach images or documents up to 5MB.</p>
                <div id="attachment-preview" class="attachment-preview-container"></div>
            </form>
        </div>
    </div>
</div>
</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reschedule Modal -->
    <div class="modal" id="reschedule-modal">
        <div class="modal-content">
            <div class="close-modal">&times;</div>
            <div class="modal-title">Reschedule Appointment</div>
            
            <div class="date-picker">
                <label for="new-date">Select New Date</label>
                <input type="date" id="new-date" required>
            </div>
            
            <div class="time-picker">
                <label for="new-time">Select New Time</label>
                <select id="new-time" required>
                    <option value="">Select a time</option>
                    <option value="9:00">9:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:00">11:00 AM</option>
                    <option value="12:00">12:00 PM</option>
                    <option value="13:00">1:00 PM</option>
                    <option value="14:00">2:00 PM</option>
                    <option value="15:00">3:00 PM</option>
                    <option value="16:00">4:00 PM</option>
                    <option value="17:00">5:00 PM</option>
                    <option value="18:00">6:00 PM</option>
                </select>
            </div>
            
            <div class="modal-buttons">
                <div class="modal-button confirm-button" id="confirm-reschedule">Confirm</div>
                <div class="modal-button cancel-button" id="cancel-reschedule">Cancel</div>
            </div>
        </div>
    </div>
    
   <!-- Cancel Modal -->
   <div class="modal" id="cancel-modal">
        <div class="modal-content">
            <div class="close-modal">&times;</div>
            <div class="modal-title">Cancel Appointment</div>
            
            <p>Are you sure you want to cancel your appointment? A cancellation fee may apply if it's within 24 hours of your scheduled time.</p>
            
            <div class="modal-buttons">
                <div class="modal-button confirm-button" id="confirm-cancel">Yes, Cancel</div>
                <div class="modal-button cancel-button" id="abort-cancel">No, Keep It</div>
            </div>
        </div>
    </div>

    <!-- Profile Edit Modal -->
    <div class="modal profile-edit-modal" id="profile-modal">
        <div class="modal-content">
            <div class="close-modal">&times;</div>
            <div class="modal-title">Edit Profile</div>
            
            <div id="profile-message"></div>
            
            <form class="profile-form" id="profile-form">
                <div class="form-group">
                    <label for="profile-first-name">First Name</label>
                    <input type="text" id="profile-first-name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="profile-last-name">Last Name</label>
                    <input type="text" id="profile-last-name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="profile-email">Email Address</label>
                    <input type="email" id="profile-email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="profile-phone">Phone Number</label>
                    <input type="tel" id="profile-phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="profile-password">New Password (leave blank to keep current)</label>
                    <input type="password" id="profile-password" name="password" placeholder="New password">
                </div>
                
                <div class="form-group">
                    <label for="profile-confirm-password">Confirm New Password</label>
                    <input type="password" id="profile-confirm-password" name="confirm_password" placeholder="Confirm new password">
                </div>
                
                <div class="form-group full-width">
    <label for="update-past-records">
        <input type="checkbox" id="update-past-records" name="update_past_records" checked>
        <span>Update all my past booking records with new information</span>
    </label>
    <p style="font-size: 12px; color: #666; margin-top: 5px;">
        If checked, all your past appointments will show your new name and contact details. 
        If unchecked, only future appointments will be updated.
    </p>
</div>
                
                <div class="profile-modal-buttons">
                    <button type="submit" class="modal-button save-profile-btn">Save Changes</button>
                    <button type="button" class="modal-button cancel-profile-btn" id="cancel-profile">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
   document.addEventListener('DOMContentLoaded', function() {
    // Navigation for header links
    document.querySelector('.nav-link').addEventListener('click', function() {
        window.location.href = 'services.php';
    });

    document.querySelector('.book-now').addEventListener('click', function() {
        window.location.href = 'booking.php';
    });

    // Tab switching functionality
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            tab.classList.add('active');
            
            // Hide all tab contents
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Show the corresponding tab content
            const tabId = tab.getAttribute('data-tab');
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });
    
    // Menu item functionality
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            // Remove active class from all menu items
            menuItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked menu item
            item.classList.add('active');
            
            // Handle menu actions
            if (item.id === 'appointments-menu') {
                // Show the appointment tabs navigation
                document.querySelector('.tabs').style.display = 'flex';
                
                // Hide all tab contents
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Show upcoming tab content
                document.getElementById('upcoming-tab').classList.add('active');
                
                tabs[0].click(); // Activate the Upcoming tab
            } else if (item.id === 'messages-menu') {
                // Hide all tab contents
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Hide the appointment tabs navigation
                document.querySelector('.tabs').style.display = 'none';
                
                // Show messages tab content
                document.getElementById('messages-tab').classList.add('active');
                
                // Load messages
                loadMessages();
            } else if (item.id === 'logout-menu') {
                // Logout functionality
                window.location.href = 'members-lounge.php?logout=1';
            }
        });
    });
    
    // Modal functionality
    const modals = document.querySelectorAll('.modal');
    const rescheduleModal = document.getElementById('reschedule-modal');
    const cancelModal = document.getElementById('cancel-modal');
    const profileModal = document.getElementById('profile-modal');
    const closeButtons = document.querySelectorAll('.close-modal');
    
    // Close modals when clicking the close button
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
        });
    });
    
    // Close modals when clicking outside the modal content
    window.addEventListener('click', (event) => {
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Cancel appointment button functionality
    document.querySelectorAll('.cancel-btn').forEach(button => {
        button.addEventListener('click', () => {
            const appointmentId = button.getAttribute('data-id');
            document.getElementById('confirm-cancel').setAttribute('data-id', appointmentId);
            cancelModal.style.display = 'flex';
        });
    });
    
    // Cancel appointment confirmation
    document.getElementById('confirm-cancel').addEventListener('click', function() {
        const appointmentId = this.getAttribute('data-id');
        
        fetch('update_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=cancel&booking_id=' + appointmentId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Your appointment has been cancelled.');
                // Reload the page to show updated booking list
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
            cancelModal.style.display = 'none';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during cancellation.');
            cancelModal.style.display = 'none';
        });
    });
    
    document.getElementById('abort-cancel').addEventListener('click', function() {
        cancelModal.style.display = 'none';
    });
    
    // Reschedule button functionality
    document.querySelectorAll('.reschedule-btn').forEach(button => {
        button.addEventListener('click', () => {
            const appointmentId = button.getAttribute('data-id');
            document.getElementById('confirm-reschedule').setAttribute('data-id', appointmentId);
            rescheduleModal.style.display = 'flex';
            
            // Set minimum date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('new-date').min = tomorrow.toISOString().split('T')[0];
        });
    });
    
    // Reschedule appointment confirmation
    document.getElementById('confirm-reschedule').addEventListener('click', function() {
        const appointmentId = this.getAttribute('data-id');
        const newDate = document.getElementById('new-date').value;
        const newTime = document.getElementById('new-time').value;
        
        if (!newDate || !newTime) {
            alert('Please select both a date and time for rescheduling.');
            return;
        }
        
        fetch('update_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=reschedule&booking_id=' + appointmentId + '&new_date=' + newDate + '&new_time=' + newTime
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Your appointment has been rescheduled successfully!');
                // Reload the page to show updated booking list
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
            rescheduleModal.style.display = 'none';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during rescheduling.');
            rescheduleModal.style.display = 'none';
        });
    });
    
    document.getElementById('cancel-reschedule').addEventListener('click', function() {
        rescheduleModal.style.display = 'none';
    });
    
    // Book again functionality
    document.querySelectorAll('.book-again-btn').forEach(button => {
        button.addEventListener('click', () => {
            const service = button.getAttribute('data-service');
            window.location.href = 'booking.php?service=' + service;
        });
    });
    
    // Edit profile button functionality
    const editProfileBtn = document.getElementById('edit-profile-btn');
    
    editProfileBtn.addEventListener('click', () => {
        profileModal.style.display = 'flex';
    });
    
    // Profile edit form submission - AJAX version
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const password = document.getElementById('profile-password').value;
        const confirmPassword = document.getElementById('profile-confirm-password').value;
        const messageDiv = document.getElementById('profile-message');
        
        // Clear previous messages
        messageDiv.innerHTML = '';
        
        if (password && password !== confirmPassword) {
            messageDiv.innerHTML = '<div class="error-message">Passwords do not match.</div>';
            return;
        }
        
        // Create FormData object
        const formData = new FormData(this);
        formData.append('action', 'update_profile');
        
        // Add checkbox value for updating past records
        const updatePastRecords = document.getElementById('update-past-records').checked;
        formData.append('update_past_records', updatePastRecords ? '1' : '0');
        
        // Show loading state
        const submitButton = this.querySelector('.save-profile-btn');
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = 'Saving...';
        submitButton.disabled = true;
        
        // Submit form via AJAX
        fetch('update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                messageDiv.innerHTML = '<div class="success-message">' + data.message + '</div>';
                
                // Update displayed user information
                document.querySelector('.profile-name').textContent = document.getElementById('profile-first-name').value + ' ' + document.getElementById('profile-last-name').value;
                document.querySelector('.profile-email').textContent = document.getElementById('profile-email').value;
                document.querySelector('.profile-phone').textContent = document.getElementById('profile-phone').value;
                
                // Update avatar if first name changed
                const newFirstLetter = document.getElementById('profile-first-name').value.charAt(0).toUpperCase();
                document.querySelector('.avatar').textContent = newFirstLetter;
                document.querySelector('.user-initial').textContent = newFirstLetter;
                
                // Update page title
                document.querySelector('.page-title').textContent = 'Welcome, ' + document.getElementById('profile-first-name').value + '!';
                
                // Close modal after 2 seconds
                setTimeout(() => {
                    profileModal.style.display = 'none';
                    messageDiv.innerHTML = '';
                }, 2000);
                
                // Handle email verification if email changed
                if (data.email_changed) {
                    setTimeout(() => {
                        alert('Your email has been changed. Please check your new email for a verification link.');
                        // Redirect to verification pending page
                        window.location.href = data.redirect || 'verification-pending.php';
                    }, 2000);
                }
            } else {
                messageDiv.innerHTML = '<div class="error-message">' + data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.innerHTML = '<div class="error-message">An error occurred. Please try again.</div>';
        })
        .finally(() => {
            // Restore button state
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        });
    });
    
    document.getElementById('cancel-profile').addEventListener('click', function() {
        profileModal.style.display = 'none';
        document.getElementById('profile-message').innerHTML = '';
    });
    
    // File attachment preview handling
    const attachmentInput = document.getElementById('message-attachment');
    const attachmentPreview = document.getElementById('attachment-preview');
    
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            attachmentPreview.innerHTML = '';
            
            if (this.files.length > 0) {
                const file = this.files[0];
                
                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB limit. Please choose a smaller file.');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                
                // Create preview element
                const previewElement = document.createElement('div');
                previewElement.className = 'attachment-preview';
                
                // Remove button
                const removeButton = document.createElement('div');
                removeButton.className = 'remove-attachment';
                removeButton.innerHTML = '×';
                removeButton.addEventListener('click', function() {
                    attachmentInput.value = '';
                    attachmentPreview.innerHTML = '';
                });
                
                if (file.type.startsWith('image/')) {
                    // For image files
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        previewElement.appendChild(img);
                        previewElement.appendChild(removeButton);
                        attachmentPreview.appendChild(previewElement);
                    };
                    reader.readAsDataURL(file);
                } else {
                    // For other files
                    const fileIcon = document.createElement('div');
                    fileIcon.className = 'file-icon';
                    fileIcon.innerHTML = '📄';
                    fileIcon.style.fontSize = '40px';
                    fileIcon.style.display = 'flex';
                    fileIcon.style.alignItems = 'center';
                    fileIcon.style.justifyContent = 'center';
                    fileIcon.style.height = '100%';
                    
                    const fileName = document.createElement('div');
                    fileName.className = 'file-name';
                    fileName.textContent = file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name;
                    fileName.style.position = 'absolute';
                    fileName.style.bottom = '0';
                    fileName.style.left = '0';
                    fileName.style.right = '0';
                    fileName.style.textAlign = 'center';
                    fileName.style.backgroundColor = 'rgba(0,0,0,0.5)';
                    fileName.style.color = 'white';
                    fileName.style.padding = '3px';
                    fileName.style.fontSize = '10px';
                    
                    previewElement.appendChild(fileIcon);
                    previewElement.appendChild(fileName);
                    previewElement.appendChild(removeButton);
                    attachmentPreview.appendChild(previewElement);
                }
            }
        });
    }
    
    // Message form submission
    const messageForm = document.getElementById('message-form');
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            const subject = document.getElementById('message-subject').value || "Re: Salon Conversation";
            const content = document.getElementById('message-content').value;
            const attachmentInput = document.getElementById('message-attachment');
            const attachment = attachmentInput && attachmentInput.files.length > 0 ? attachmentInput.files[0] : null;
            
            if (!content) {
                alert('Please enter a message');
                return false;
            }
            
            // Disable the button and show loading state
            const sendButton = document.getElementById('send-message');
            const originalHTML = sendButton.innerHTML;
            sendButton.disabled = true;
            sendButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
            
            // Create FormData
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('subject', subject);
            formData.append('content', content);
            
            if (attachment) {
                // Check file size (max 5MB)
                if (attachment.size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB limit. Please choose a smaller file.');
                    sendButton.disabled = false;
                    sendButton.innerHTML = originalHTML;
                    return false;
                }
                
                formData.append('attachment', attachment);
            }
            
            // Send using fetch
            fetch('chat.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    // Clear form
                    document.getElementById('message-content').value = '';
                    if (attachmentInput) {
                        attachmentInput.value = '';
                    }
                    document.getElementById('attachment-preview').innerHTML = '';
                    
                    // Reload messages
                    loadMessages();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending your message: ' + error.message);
            })
            .finally(() => {
                // Always restore button regardless of success/failure
                sendButton.disabled = false;
                sendButton.innerHTML = originalHTML;
            });
            
            return false;
        });
    }
    
    // Function to load messages from the server
    function loadMessages() {
        const messageList = document.getElementById('message-list');
        messageList.innerHTML = '<div class="loading-messages">Loading your conversation...</div>';
        
        fetch('chat.php?action=get_messages')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (data.messages.length === 0) {
                        messageList.innerHTML = `
                            <div class="no-messages">
                                <div class="no-appointments-icon">💬</div>
                                <p>You don't have any messages yet. Send a message to start a conversation.</p>
                            </div>
                        `;
                    } else {
                        let messagesHTML = '';
                        let currentDate = '';
                        
                        // Sort messages by date (oldest to newest)
                        data.messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                        
                        data.messages.forEach(message => {
                            // Format message date
                            const messageDate = new Date(message.created_at);
                            const today = new Date();
                            const yesterday = new Date(today);
                            yesterday.setDate(yesterday.getDate() - 1);
                            
                            // Format date for display
                            let dateStr;
                            if (messageDate.toDateString() === today.toDateString()) {
                                dateStr = 'Today';
                            } else if (messageDate.toDateString() === yesterday.toDateString()) {
                                dateStr = 'Yesterday';
                            } else {
                                dateStr = messageDate.toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'short', 
                                    day: 'numeric' 
                                });
                            }
                            
                            // Add date separator if day changes
                            if (dateStr !== currentDate) {
                                messagesHTML += `
                                    <div class="date-separator">
                                        <span class="date-text">${dateStr}</span>
                                    </div>
                                `;
                                currentDate = dateStr;
                            }
                            
                            const fromSalon = message.sender_type === 'salon';
                            const messageTime = messageDate.toLocaleTimeString('en-US', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            
                            let attachmentsHTML = '';
                            
                            // Add attachments if present
                            if (message.has_attachment && message.attachments && message.attachments.length > 0) {
                                attachmentsHTML = '<div class="message-attachments">';
                                
                                message.attachments.forEach(attachment => {
                                    // Determine if it's an image or other file
                                    const isImage = ['image/jpeg', 'image/png', 'image/gif'].includes(attachment.file_type);
                                    
                                    if (isImage) {
                                        attachmentsHTML += `
                                            <div class="attachment-file">
                                                <a href="${attachment.file_path}" target="_blank">
                                                    <img src="${attachment.file_path}" alt="${attachment.file_name}" class="attachment-image" style="max-width: 150px; max-height: 100px;">
                                                </a>
                                            </div>
                                        `;
                                    } else {
                                        // For non-image files
                                        attachmentsHTML += `
                                            <div class="attachment-file">
                                                <a href="${attachment.file_path}" target="_blank" class="attachment-link">
                                                    <span class="attachment-icon">📎</span>
                                                    <span class="attachment-name">${attachment.file_name}</span>
                                                </a>
                                            </div>
                                        `;
                                    }
                                });
                                
                                attachmentsHTML += '</div>';
                            }
                            
                            // Only show the subject on the first message of a conversation
                            const showSubject = message.id === data.messages[0].id || 
                                               (data.messages.indexOf(message) > 0 && 
                                                data.messages[data.messages.indexOf(message) - 1].sender_type !== message.sender_type);
                            
                            const subjectHTML = showSubject && message.subject ? 
                                `<div class="message-subject">${message.subject}</div>` : '';
                            
                            messagesHTML += `
                                <div class="message-item ${fromSalon ? 'from-salon' : 'from-user'} ${message.read_status === 0 ? 'unread' : ''}" data-id="${message.id}">
                                    ${subjectHTML}
                                    <div class="message-content">${message.content}</div>
                                    ${attachmentsHTML}
                                    <div class="message-time">${messageTime}</div>
                                </div>
                            `;
                        });
                        
                        messageList.innerHTML = messagesHTML;
                        
                        // Scroll to the bottom of the message list
                        messageList.scrollTop = messageList.scrollHeight;
                        
                        // Mark messages as read when viewed
                        document.querySelectorAll('.message-item.unread.from-salon').forEach(message => {
                            const messageId = message.getAttribute('data-id');
                            markMessageAsRead(messageId);
                        });
                        
                        // Hide subject field if there are existing messages
                        const subjectField = document.getElementById('message-subject');
                        if (data.messages.length > 0) {
                            subjectField.style.display = 'none';
                            subjectField.removeAttribute('required');
                        } else {
                            subjectField.style.display = 'block';
                            subjectField.setAttribute('required', 'required');
                        }
                    }
                } else {
                    messageList.innerHTML = `
                        <div class="no-messages">
                            <p>Error loading messages: ${data.message || 'Unknown error'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageList.innerHTML = `
                    <div class="no-messages">
                        <p>Error loading messages. Please try again later.</p>
                    </div>
                `;
            });
    }
    
    // Function to mark a message as read
    function markMessageAsRead(messageId) {
        fetch('chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=mark_read&message_id=' + messageId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Message marked as read:', messageId);
            }
        })
        .catch(error => {
            console.error('Error marking message as read:', error);
        });
    }
    
    // Add event listener to the date field to load available times
    document.getElementById('new-date').addEventListener('change', function() {
        const selectedDate = this.value;
        if (selectedDate) {
            // Fetch available times for the selected date
            fetch('get-available-times.php?date=' + selectedDate)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Get the time dropdown
                        const timeSelect = document.getElementById('new-time');
                        // Clear existing options
                        timeSelect.innerHTML = '<option value="">Select a time</option>';
                        
                        // Add available time slots as options
                        const availableTimes = data.available_times;
                        for (const [value, label] of Object.entries(availableTimes)) {
                            const option = document.createElement('option');
                            option.value = value;
                            option.textContent = label;
                            timeSelect.appendChild(option);
                        }
                        
                        // If no times available, show message
                        if (Object.keys(availableTimes).length === 0) {
                            const option = document.createElement('option');
                            option.disabled = true;
                            option.selected = true;
                            option.textContent = "No available times for this date";
                            timeSelect.appendChild(option);
                        }
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    });
});
</script>
</body>
</html>