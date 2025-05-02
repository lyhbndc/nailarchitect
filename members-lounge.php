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
            max-width: 1200px;
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
        
        .logo-container img {
            height: 60px;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav-link {
            cursor: pointer;
        }
        
        .book-now {
            padding: 8px 20px;
            background-color: #e8d7d0;
            border-radius: 20px;
            cursor: pointer;
        }
        
        .user-initial {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #e0c5b7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
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
            background-color: #e8d7d0;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
        }
        
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #e0c5b7;
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
            background-color: #d9bbb0;
            display: inline-block;
        }
        
        .action-button:hover {
            background-color: #ae9389;
        }
        
        .menu-card {
            background-color: #e8d7d0;
            border-radius: 15px;
            padding: 20px;
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
            background-color: #d9bbb0;
        }
        
        .menu-item.active {
            background-color: #ae9389;
            font-weight: bold;
        }
        
        .menu-icon {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            background-color: #e8d7d0;
            border-radius: 15px;
            padding: 30px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 1px solid #c0c0c0;
            padding-bottom: 10px;
        }
        
        .appointments-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
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
        
        .appointment-header {
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
            background-color: #ae9389;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #fff;
        }
        
        .tab.active {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #333;
        }
        
        .tab-content {
            display: none;
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
        
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: #ffffff;
            font-family: 'Courier New', monospace;
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
            background-color: #e8d7d0;
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
            background-color: #d9bbb0;
        }
        
        .close-modal:hover {
            background-color: #ae9389;
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
    </style>
</head>
<body>
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
                        <div class="section-title">Upcoming Appointments</div>
                        
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
                    
                    <div class="tab-content" id="past-tab">
                        <div class="section-title">Past Appointments</div>
                        
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
    <div class="modal" id="profile-modal">
        <div class="modal-content">
            <div class="close-modal">&times;</div>
            <div class="modal-title">Edit Profile</div>
            
            <form class="contact-form" id="profile-form" method="POST" action="update_profile.php">
                <div class="form-group">
                    <label for="profile-first-name">First Name</label>
                    <input type="text" id="profile-first-name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="profile-last-name">Last Name</label>
                    <input type="text" id="profile-last-name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="profile-email">Email Address</label>
                    <input type="email" id="profile-email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="profile-phone">Phone Number</label>
                    <input type="tel" id="profile-phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="profile-password">Change Password</label>
                    <input type="password" id="profile-password" name="password" placeholder="New password">
                </div>
                
                <div class="form-group">
                    <label for="profile-confirm-password">Confirm Password</label>
                    <input type="password" id="profile-confirm-password" name="confirm_password" placeholder="Confirm new password">
                </div>
                
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                
                <div class="modal-buttons">
                    <div class="modal-button confirm-button" id="save-profile">Save Changes</div>
                    <div class="modal-buttons">
                    <div class="modal-button confirm-button" id="save-profile">Save Changes</div>
                    <div class="modal-button cancel-button" id="cancel-profile">Cancel</div>
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
                   tabs[0].click(); // Activate the Upcoming tab
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
       
       // Profile edit form submission
       document.getElementById('save-profile').addEventListener('click', function() {
           const formElement = document.getElementById('profile-form');
           const password = document.getElementById('profile-password').value;
           const confirmPassword = document.getElementById('profile-confirm-password').value;
           
           if (password && password !== confirmPassword) {
               alert('Passwords do not match.');
               return;
           }
           
           // Submit the form
           formElement.submit();
       });
       
       document.getElementById('cancel-profile').addEventListener('click', function() {
           profileModal.style.display = 'none';
       });
   });
</script>
</body>
</html>