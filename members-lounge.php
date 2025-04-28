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
        }
        
        .tab.active {
            background-color: #f0f0f0;
            font-weight: bold;
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
                            <div class="appointment-card" data-id="appointment-1">
                                <div class="appointment-header">
                                    <div class="appointment-service">Gel Manicure</div>
                                    <div class="appointment-status status-confirmed">Confirmed</div>
                                </div>
                                
                                <div class="appointment-details">
                                    <div class="detail-item">
                                        <div class="detail-label">Date</div>
                                        <div class="detail-value">April 30, 2025</div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Time</div>
                                        <div class="detail-value">2:00 PM</div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Duration</div>
                                        <div class="detail-value">60 minutes</div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Technician</div>
                                        <div class="detail-value">Jessica</div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Price</div>
                                        <div class="detail-value">$45</div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Reference</div>
                                        <div class="detail-value">#NAI-2025042</div>
                                    </div>
                                </div>
                                
                                <div class="appointment-actions">
                                    <div class="action-button reschedule-btn" data-id="appointment-1">Reschedule</div>
                                    <div class="action-button cancel-btn" data-id="appointment-1">Cancel</div>
                                </div>
                            </div>
                            
                            <!-- You can replace these static appointments with data from your database -->
                        </div>
                    </div>
                    
                    <div class="tab-content" id="past-tab">
                        <div class="section-title">Past Appointments</div>
                        
                        <div class="appointments-list" id="past-appointments">
                            <div class="appointment-card" data-id="appointment-3">
                                <div class="appointment-header">
                                    <div class="appointment-service">Classic Manicure</div>
                                    <div class="appointment-status status-completed">Completed</div>
                                </div>
                                
                                <div class="appointment-details">
                                    <div class="detail-item">
                                        <div class="detail-label">Date</div>
                                        <div class="detail-value">March 15, 2025</div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Time</div>
                                        <div class="detail-value">3:30 PM</div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Duration</div>
                                        <div class="detail-value">45 minutes</div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Technician</div>
                                        <div class="detail-value">Maria</div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Price</div>
                                        <div class="detail-value">$35</div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Reference</div>
                                        <div class="detail-value">#NAI-2025021</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- You can replace these static appointments with data from your database -->
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
                    window.location.href = 'login.php?logout=1';
                }
            });
        });
        
        // Modal functionality
        const modals = document.querySelectorAll('.modal');
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
        
        // Reschedule button functionality
        const rescheduleButtons = document.querySelectorAll('.reschedule-btn');
        const rescheduleModal = document.getElementById('reschedule-modal');
        
        rescheduleButtons.forEach(button => {
            button.addEventListener('click', () => {
                rescheduleModal.style.display = 'flex';
                
                // Set minimum date to tomorrow
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                document.getElementById('new-date').min = tomorrow.toISOString().split('T')[0];
            });
        });
        
        // Cancel appointment button functionality
        const cancelButtons = document.querySelectorAll('.cancel-btn');
        const cancelModal = document.getElementById('cancel-modal');
        
        cancelButtons.forEach(button => {
            button.addEventListener('click', () => {
                cancelModal.style.display = 'flex';
            });
        });
        
        // Edit profile button functionality
        const editProfileBtn = document.getElementById('edit-profile-btn');
        const profileModal = document.getElementById('profile-modal');
        
        editProfileBtn.addEventListener('click', () => {
            profileModal.style.display = 'flex';
        });
        
        // Modal confirmation handlers
        document.getElementById('confirm-reschedule').addEventListener('click', function() {
            const newDate = document.getElementById('new-date').value;
            const newTime = document.getElementById('new-time').value;
            
            if (!newDate || !newTime) {
                alert('Please select both a date and time for rescheduling.');
                return;
            }
            
            alert('Your appointment has been rescheduled successfully!');
            rescheduleModal.style.display = 'none';
            
            // In a real app, this would send the data to the server and update the UI
        });
        
        document.getElementById('cancel-reschedule').addEventListener('click', function() {
            rescheduleModal.style.display = 'none';
        });
        
        document.getElementById('confirm-cancel').addEventListener('click', function() {
            alert('Your appointment has been cancelled.');
            cancelModal.style.display = 'none';
            
            // In a real app, this would send the data to the server and update the UI
        });
        
        document.getElementById('abort-cancel').addEventListener('click', function() {
            cancelModal.style.display = 'none';
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