<?php
// Start session and database connection
session_start();
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// Chec
// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}


// Set default filter to 'upcoming' if not specified
$view_filter = isset($_GET['view']) ? $_GET['view'] : 'upcoming';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Handle search query
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_condition = '';

if (!empty($search_query)) {
    $search_condition = " AND (
        u.first_name LIKE '%$search_query%' OR 
        u.last_name LIKE '%$search_query%' OR 
        b.name LIKE '%$search_query%' OR 
        b.reference_id LIKE '%$search_query%' OR
        b.phone LIKE '%$search_query%'
    )";
}

// Build the query based on the filter
if ($view_filter == 'upcoming') {
    if ($status_filter == 'all') {
        $appointments_query = "SELECT b.*, u.first_name, u.last_name 
                              FROM bookings b 
                              LEFT JOIN users u ON b.user_id = u.id 
                              WHERE b.date >= CURDATE() AND b.status != 'completed' $search_condition
                              ORDER BY b.date ASC, b.time ASC";
    } else {
        $status = mysqli_real_escape_string($conn, $status_filter);
        $appointments_query = "SELECT b.*, u.first_name, u.last_name 
                              FROM bookings b 
                              LEFT JOIN users u ON b.user_id = u.id 
                              WHERE b.date >= CURDATE() AND b.status = '$status' $search_condition
                              ORDER BY b.date ASC, b.time ASC";
    }
} else { // past appointments
    if ($status_filter == 'all') {
        // For "Past" view, show completed appointments and appointments with past dates
        $appointments_query = "SELECT b.*, u.first_name, u.last_name 
                            FROM bookings b 
                            LEFT JOIN users u ON b.user_id = u.id 
                            WHERE (b.date < CURDATE() OR b.status = 'completed') $search_condition
                            ORDER BY b.date DESC, b.time DESC";
    } else {
        $status = mysqli_real_escape_string($conn, $status_filter);
        // For specific status filters in "Past" view
        $appointments_query = "SELECT b.*, u.first_name, u.last_name 
                            FROM bookings b 
                            LEFT JOIN users u ON b.user_id = u.id 
                            WHERE (b.date < CURDATE() OR b.status = '$status') $search_condition
                            ORDER BY b.date DESC, b.time DESC";
    }
}
$appointments = mysqli_query($conn, $appointments_query);

// Get statistics
$total_appointments_query = "SELECT COUNT(*) as total FROM bookings";
$confirmed_appointments_query = "SELECT COUNT(*) as confirmed FROM bookings WHERE status = 'confirmed'";
$pending_appointments_query = "SELECT COUNT(*) as pending FROM bookings WHERE status = 'pending'";
$cancelled_appointments_query = "SELECT COUNT(*) as cancelled FROM bookings WHERE status = 'cancelled'";

$total_appointments = mysqli_query($conn, $total_appointments_query)->fetch_assoc()['total'];
$confirmed_appointments = mysqli_query($conn, $confirmed_appointments_query)->fetch_assoc()['confirmed'];
$pending_appointments = mysqli_query($conn, $pending_appointments_query)->fetch_assoc()['pending'];
$cancelled_appointments = mysqli_query($conn, $cancelled_appointments_query)->fetch_assoc()['cancelled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nail Architect - Admin Appointments</title>
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
        
        .logo i {
            font-size: 22px;
            z-index: 1;
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
            text-decoration: none;
            color: inherit;
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
        
        .search-container {
            width: 300px;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 8px 15px 8px 35px;
            border-radius: 20px;
            border: none;
            background-color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }
        
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }
        
        .search-input:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        
        /* View tabs (Upcoming/Past) */
        .view-tabs {
            display: flex;
            gap: 0;
            background-color: #FFF;
            border-radius: 8px;
            padding: 5px;
            margin-bottom: 20px;
            width: fit-content;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .view-tab {
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            color: inherit;
            border-radius: 5px;
        }
        
        .view-tab.active {
            background-color: #E8D7D0;
            font-weight: 600;
        }
        
        /* Filter tabs (All, Confirmed, etc.) */
        .filter-tabs {
            display: flex;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .filter-tab {
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            position: relative;
            text-decoration: none;
            color: inherit;
            font-weight: 500;
        }
        
        .filter-tab.active {
            font-weight: 600;
        }
        
        .filter-tab.active::after {
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #333;
        }
        
        .filter-tab:hover {
            background-color: rgba(0,0,0,0.03);
        }
        
        /* Appointment cards */
        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }
        
        .appointment-card {
            background-color: #FFF;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .appointment-service {
            padding: 20px;
            font-size: 18px;
            font-weight: 600;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            position: relative;
        }
        
        .status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-confirmed {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .status-pending {
            background-color: #fff9c4;
            color: #f57f17;
        }
        
        .status-cancelled {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .status-completed {
            background-color: #e0e0e0;
            color: #616161;
        }
        
        .appointment-details {
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-row-gap: 15px;
            grid-column-gap: 10px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 14px;
            font-weight: 500;
        }
        
        .notes-section {
            padding: 0 20px 15px;
            margin-top: -10px;
        }
        
        .notes-label {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .notes-content {
            background-color: rgba(0,0,0,0.02);
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            color: #555;
            min-height: 40px;
            width: 100%;
        }
        
        .appointment-actions {
            padding: 15px 20px;
            background-color: rgba(0,0,0,0.02);
            display: flex;
            justify-content: space-between;
            gap: 10px;
            border-top: 1px solid rgba(0,0,0,0.06);
            margin-top: auto;
        }
        
        .action-btns {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .view-btn {
            background-color: #E8D7D0;
            color: #333;
        }
        
        .view-btn:hover {
            background-color: #D9BBB0;
        }
        
        .approve-btn {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .approve-btn:hover {
            background-color: #b7d9b7;
        }
        
        .reject-btn {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .reject-btn:hover {
            background-color: #ebbcbf;
        }
        
        .complete-btn {
            background-color: #E8D7D0;
            color: #333;
        }
        
        .complete-btn:hover {
            background-color: #D9BBB0;
        }
        
        .no-appointments {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            background-color: rgba(255,255,255,0.5);
            border-radius: 10px;
        }
        
        .no-appointments i {
            font-size: 40px;
            color: #ccc;
            margin-bottom: 15px;
        }
        
        .no-appointments p {
            font-size: 16px;
            color: #888;
        }
        
        /* Appointment Details Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: #FFF;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .close-modal {
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-section {
            margin-bottom: 20px;
        }
        
        .modal-section-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #666;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .detail-box {
            background-color: rgba(0,0,0,0.02);
            padding: 10px 15px;
            border-radius: 8px;
        }
        
        .detail-box-label {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
        }
        
        .detail-box-value {
            font-size: 15px;
            font-weight: 500;
        }
        
        .notes-box {
            background-color: rgba(0,0,0,0.02);
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
        }
        
        /* Image Gallery Modal */
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            z-index: 1100;
            justify-content: center;
            align-items: center;
        }
        
        .gallery-content {
            background-color: #FFF;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .gallery-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .gallery-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .close-gallery {
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .gallery-body {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .gallery-item {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: scale(1.02);
        }
        
        .gallery-item img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        /* Loading overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1200;
            justify-content: center;
            align-items: center;
        }
        
        .loading-spinner {
            background-color: white;
            padding: 20px 30px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
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
            
            .search-container {
                width: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .appointments-grid {
                grid-template-columns: 1fr;
            }
            
            .content-wrapper {
                padding: 15px;
                padding-top: 70px;
            }
            
            .view-tabs {
                width: 100%;
                justify-content: space-between;
            }
            
            .view-tab {
                flex: 1;
                text-align: center;
            }
            
            .appointment-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-btns {
                justify-content: center;
            }
            
            .gallery-body {
                grid-template-columns: 1fr;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .top-bar {
                flex-direction: column;
                height: auto;
                padding: 10px 15px;
                gap: 10px;
            }
            
            .search-container {
                width: 100%;
            }
        }
        /* Add these styles to your existing <style> section */
.appointment-actions {
    padding: 15px 20px;
    background-color: rgba(0,0,0,0.02);
    border-top: 1px solid rgba(0,0,0,0.06);
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.action-btns-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.view-btns {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.action-btns {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.action-btn {
    padding: 8px 15px;
    border: none;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    min-width: 110px;
    justify-content: center;
}

.view-btn {
    background-color: #f0e6e2;
    color: #333;
}

.view-btn:hover {
    background-color: #e0d6d2;
}

.approve-btn {
    background-color: #c8e6c9;
    color: #2e7d32;
    min-width: 120px;
}

.approve-btn:hover {
    background-color: #b7d9b7;
}

.reject-btn {
    background-color: #ffcdd2;
    color: #c62828;
    min-width: 120px;
}

.reject-btn:hover {
    background-color: #ebbcbf;
}

.complete-btn {
    background-color: #c8e6c9;
    color: #2e7d32;
    min-width: 140px;
}

.complete-btn:hover {
    background-color: #b7d9b7;
}

/* Mobile responsiveness improvements */
@media (max-width: 768px) {
    .appointment-actions {
        padding: 15px;
    }
    
    .action-btns-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .view-btns, .action-btns {
        width: 100%;
        justify-content: space-between;
    }
    
    .action-btn {
        flex: 1;
        min-width: unset;
    }
}

@media (max-width: 480px) {
    .view-btns, .action-btns {
        flex-direction: column;
    }
    
    .action-btn {
        width: 100%;
    }
}
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-spa"></i>
            </div>
            <div class="admin-title">Admin</div>
        </div>
        
        <div class="nav-menu">
            <div class="menu-section">MAIN</div>
            
            <a href="admin-dashboard.php" class="menu-item">
                <div class="menu-icon"><i class="fas fa-tachometer-alt"></i></div>
                <div class="menu-text">Dashboard</div>
            </a>
            
            <a href="admin-appointments.php" class="menu-item active">
                <div class="menu-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="menu-text">Appointments</div>
            </a>
            
            <a href="admin-clients.php" class="menu-item">
                <div class="menu-icon"><i class="fas fa-users"></i></div>
                <div class="menu-text">Clients</div>
            </a>
            
            <a href="admin-messages.php" class="menu-item">
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
        <div class="page-title">
            <?php echo ($view_filter == 'upcoming') ? 'Upcoming Appointments' : 'Past Appointments'; ?>
        </div>
        
        <form action="" method="GET" class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="hidden" name="view" value="<?php echo $view_filter; ?>">
            <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
            <input type="text" name="search" placeholder="Search client, reference or phone..." class="search-input" value="<?php echo htmlspecialchars($search_query); ?>">
        </form>
    </div>
    
    <div class="content-wrapper">
        <!-- View tabs (Upcoming/Past) -->
        <div class="view-tabs">
            <a href="?view=upcoming<?php echo ($status_filter != 'all') ? '&status='.$status_filter : ''; ?><?php echo (!empty($search_query)) ? '&search='.$search_query : ''; ?>" class="view-tab <?php echo ($view_filter == 'upcoming') ? 'active' : ''; ?>">Upcoming</a>
            <a href="?view=past<?php echo ($status_filter != 'all') ? '&status='.$status_filter : ''; ?><?php echo (!empty($search_query)) ? '&search='.$search_query : ''; ?>" class="view-tab <?php echo ($view_filter == 'past') ? 'active' : ''; ?>">Past</a>
        </div>
        
      <!-- Filter tabs -->
<div class="filter-tabs">
    <?php if ($view_filter == 'upcoming'): ?>
        <!-- Upcoming view shows all options except completed -->
        <a href="?view=<?php echo $view_filter; ?>&status=all<?php echo (!empty($search_query)) ? '&search='.$search_query : ''; ?>" class="filter-tab <?php echo ($status_filter == 'all') ? 'active' : ''; ?>">All</a>
        <a href="?view=<?php echo $view_filter; ?>&status=confirmed<?php echo (!empty($search_query)) ? '&search='.$search_query : ''; ?>" class="filter-tab <?php echo ($status_filter == 'confirmed') ? 'active' : ''; ?>">Confirmed</a>
        <a href="?view=<?php echo $view_filter; ?>&status=pending<?php echo (!empty($search_query)) ? '&search='.$search_query : ''; ?>" class="filter-tab <?php echo ($status_filter == 'pending') ? 'active' : ''; ?>">Pending</a>
        <a href="?view=<?php echo $view_filter; ?>&status=cancelled<?php echo (!empty($search_query)) ? '&search='.$search_query : ''; ?>" class="filter-tab <?php echo ($status_filter == 'cancelled') ? 'active' : ''; ?>">Cancelled</a>
    <?php else: ?>
        <!-- Past view shows only completed option (and All for showing all completed) -->
        <a href="?view=<?php echo $view_filter; ?>&status=all<?php echo (!empty($search_query)) ? '&search='.$search_query : ''; ?>" class="filter-tab <?php echo ($status_filter == 'all') ? 'active' : ''; ?>">All Completed</a>
    <?php endif; ?>
</div>
        
        <!-- Appointments grid -->
        <div class="appointments-grid">
            <?php if (mysqli_num_rows($appointments) > 0): ?>
                <?php while ($appointment = mysqli_fetch_assoc($appointments)): 
                    // Format service name
                    $service_name = ucfirst(str_replace('-', ' ', $appointment['service']));
                    
                    // Get client name
                    if ($appointment['user_id']) {
                        $client_name = $appointment['first_name'] . ' ' . $appointment['last_name'];
                    } else {
                        $client_name = $appointment['name'];
                    }
                    
                    // Check if this appointment has any images
                    $has_images_query = "SELECT COUNT(*) as count FROM booking_images WHERE booking_id = " . $appointment['id'];
                    $has_images_result = mysqli_query($conn, $has_images_query);
                    $has_images = $has_images_result->fetch_assoc()['count'] > 0;
                    
                    // Check if this appointment has payment proof
                    $has_payment_query = "SELECT COUNT(*) as count FROM payment_proofs WHERE booking_id = " . $appointment['id'];
                    $has_payment_result = mysqli_query($conn, $has_payment_query);
                    $has_payment = $has_payment_result->fetch_assoc()['count'] > 0;
                ?>
                    <div class="appointment-card">
    <div class="appointment-service">
        <?php echo htmlspecialchars($service_name); ?>
        <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
            <?php echo ucfirst($appointment['status']); ?>
        </span>
    </div>
    
    <div class="appointment-details">
        <div class="detail-item">
            <div class="detail-label">Date</div>
            <div class="detail-value"><?php echo date('M j, Y', strtotime($appointment['date'])); ?></div>
        </div>
        
        <div class="detail-item">
            <div class="detail-label">Time</div>
            <div class="detail-value"><?php echo date('g:i A', strtotime($appointment['time'])); ?></div>
        </div>
        
        <div class="detail-item">
            <div class="detail-label">Duration</div>
            <div class="detail-value"><?php echo $appointment['duration'] ?? '60'; ?> minutes</div>
        </div>
        
        <div class="detail-item">
            <div class="detail-label">Price</div>
            <div class="detail-value">₱<?php echo number_format($appointment['price']); ?></div>
        </div>
        
        <div class="detail-item">
            <div class="detail-label">Client</div>
            <div class="detail-value"><?php echo htmlspecialchars($client_name); ?></div>
        </div>
        
        <div class="detail-item">
            <div class="detail-label">Reference</div>
            <div class="detail-value">
                <?php 
                if (!empty($appointment['reference_id'])) {
                    echo "#" . htmlspecialchars($appointment['reference_id']); 
                } else {
                    echo "<span style='color: #c62828;'>No Ref</span>";
                }
                ?>
            </div>
        </div>
    </div>
    
    <div class="notes-section">
        <div class="notes-label">Notes:</div>
        <div class="notes-content">
            <?php echo !empty($appointment['notes']) ? htmlspecialchars($appointment['notes']) : 'No notes provided'; ?>
        </div>
    </div>
    
    <div class="appointment-actions">
    <div class="action-btns-row">
        <div class="view-btns">
            <?php if ($has_images): ?>
                <button class="action-btn view-btn show-images-btn" data-id="<?php echo $appointment['id']; ?>">
                    <i class="fas fa-images"></i> View Images
                </button>
            <?php endif; ?>
            
            <?php if ($has_payment): ?>
                <button class="action-btn view-btn show-payment-btn" data-id="<?php echo $appointment['id']; ?>">
                    <i class="fas fa-receipt"></i> View Payment
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($view_filter == 'upcoming'): ?>
        <?php if ($appointment['status'] == 'pending' || $appointment['status'] == 'confirmed'): ?>
            <div class="action-btns-row">
                <div class="action-btns">
                    <?php if ($appointment['status'] == 'pending'): ?>
                        <button class="action-btn approve-btn" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'confirmed')">
                            <i class="fas fa-check"></i> Accept
                        </button>
                        <button class="action-btn reject-btn" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'cancelled')">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    <?php elseif ($appointment['status'] == 'confirmed'): ?>
                        <button class="action-btn complete-btn" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'completed')">
                            <i class="fas fa-check-double"></i> Complete
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
</div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-appointments">
                    <i class="fas fa-calendar-times"></i>
                    <?php if (!empty($search_query)): ?>
                        <p>No appointments found matching "<?php echo htmlspecialchars($search_query); ?>".</p>
                        <a href="?view=<?php echo $view_filter; ?>&status=<?php echo $status_filter; ?>" style="color: #666; text-decoration: underline; margin-top: 10px; display: inline-block;">Clear search</a>
                    <?php else: ?>
                        <p>No appointments found matching the selected criteria.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Inspiration Images Modal -->
    <div class="image-modal" id="images-modal">
        <div class="gallery-content">
            <div class="gallery-header">
                <div class="gallery-title">Inspiration Images</div>
                <div class="close-gallery" onclick="closeGallery('images-modal')">&times;</div>
            </div>
            <div class="gallery-body" id="images-gallery">
                <!-- Images will be loaded here -->
                <div style="grid-column: 1/-1; text-align: center; padding: 30px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Loading images...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Proof Modal -->
    <div class="image-modal" id="payment-modal">
        <div class="gallery-content">
            <div class="gallery-header">
                <div class="gallery-title">Payment Proof</div>
                <div class="close-gallery" onclick="closeGallery('payment-modal')">&times;</div>
            </div>
            <div class="gallery-body" id="payment-gallery">
                <!-- Payment images will be loaded here -->
                <div style="grid-column: 1/-1; text-align: center; padding: 30px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Loading payment proof...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Appointment Details Modal -->
    <div class="modal" id="details-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Appointment Details</div>
                <div class="close-modal" onclick="closeModal('details-modal')">&times;</div>
            </div>
            <div class="modal-body" id="details-content">
                <!-- Content will be loaded here -->
                <div style="text-align: center; padding: 30px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Loading details...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
            <span id="loading-message">Processing...</span>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit search form when typing
            const searchInput = document.querySelector('.search-input');
            let searchTimeout;
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        this.form.submit();
                    }, 500); // Submit after 500ms of inactivity
                });
            }
            
            // Show inspiration images
            const showImagesButtons = document.querySelectorAll('.show-images-btn');
            showImagesButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const appointmentId = this.getAttribute('data-id');
                    showInspirationImages(appointmentId);
                });
            });
            
            // Show payment proof
            const showPaymentButtons = document.querySelectorAll('.show-payment-btn');
            showPaymentButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const appointmentId = this.getAttribute('data-id');
                    showPaymentProof(appointmentId);
                });
            });
        });
        
        // Function to show inspiration images
        function showInspirationImages(appointmentId) {
            // Clear gallery
            document.getElementById('images-gallery').innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 30px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Loading images...</p>
                </div>
            `;
            
            // Show modal
            document.getElementById('images-modal').style.display = 'flex';
            
            // Fetch images
            fetch('get-inspiration-images.php?id=' + appointmentId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.images.length > 0) {
                        let galleryHTML = '';
                        
                        data.images.forEach(image => {
                            galleryHTML += `
                                <div class="gallery-item">
                                    <img src="${image}" alt="Inspiration Image" loading="lazy">
                                </div>
                            `;
                        });
                        
                        document.getElementById('images-gallery').innerHTML = galleryHTML;
                    } else {
                        document.getElementById('images-gallery').innerHTML = `
                            <div style="grid-column: 1/-1; text-align: center; padding: 30px;">
                                <i class="fas fa-image" style="font-size: 40px; color: #ccc; margin-bottom: 15px;"></i>
                                <p>No inspiration images found.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('images-gallery').innerHTML = `
                        <div style="grid-column: 1/-1; text-align: center; padding: 30px;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 40px; color: #c62828; margin-bottom: 15px;"></i>
                            <p>Error loading images. Please try again.</p>
                        </div>
                    `;
                });
        }
        
        // Function to show payment proof
        function showPaymentProof(appointmentId) {
            // Clear gallery
            document.getElementById('payment-gallery').innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 30px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Loading payment proof...</p>
                </div>
            `;
            
            // Show modal
            document.getElementById('payment-modal').style.display = 'flex';
            
            // Fetch payment proof
            fetch('get-payment-proofs.php?id=' + appointmentId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.images.length > 0) {
                        let galleryHTML = '';
                        
                        data.images.forEach(image => {
                            galleryHTML += `
                                <div class="gallery-item">
                                    <img src="${image}" alt="Payment Proof" loading="lazy">
                                </div>
                            `;
                        });
                        
                        document.getElementById('payment-gallery').innerHTML = galleryHTML;
                    } else {
                        document.getElementById('payment-gallery').innerHTML = `
                            <div style="grid-column: 1/-1; text-align: center; padding: 30px;">
                                <i class="fas fa-file-invoice-dollar" style="font-size: 40px; color: #ccc; margin-bottom: 15px;"></i>
                                <p>No payment proof found.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('payment-gallery').innerHTML = `
                        <div style="grid-column: 1/-1; text-align: center; padding: 30px;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 40px; color: #c62828; margin-bottom: 15px;"></i>
                            <p>Error loading payment proof. Please try again.</p>
                        </div>
                    `;
                });
        }
        
        // Function to close gallery modal
        function closeGallery(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Function to close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Function to update appointment status
        function updateStatus(appointmentId, status) {
            // Confirmation messages based on status
            let confirmationMessage = '';
            switch(status) {
                case 'confirmed':
                    confirmationMessage = 'Are you sure you want to accept this appointment?';
                    break;
                case 'cancelled':
                    confirmationMessage = 'Are you sure you want to reject this appointment?';
                    break;
                case 'completed':
                    confirmationMessage = 'Are you sure you want to mark this appointment as completed?';
                    break;
                default:
                    confirmationMessage = 'Are you sure you want to update this appointment?';
            }
            
            if (!confirm(confirmationMessage)) {
                return;
            }
            
            // Show loading overlay
            showLoading('Updating appointment status...');
            
            // Create form data
            const formData = new FormData();
            formData.append('id', appointmentId);
            formData.append('status', status);
            
            // Send AJAX request
            fetch('update-appointment-status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading overlay
                hideLoading();
                
                if (data.success) {
                    // Show success message
                    showSuccessMessage('Appointment updated successfully!');
                    
                    // Reload page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                // Hide loading overlay
                hideLoading();
                
                console.error('Error:', error);
                alert('An error occurred while updating the appointment.');
            });
        }
        
        // Function to show loading overlay
        function showLoading(message = 'Processing...') {
            document.getElementById('loading-message').textContent = message;
            document.getElementById('loading-overlay').style.display = 'flex';
        }
        
        // Function to hide loading overlay
        function hideLoading() {
            document.getElementById('loading-overlay').style.display = 'none';
        }
        
        // Function to show success message
        function showSuccessMessage(message) {
            const successMessage = document.createElement('div');
            successMessage.style.position = 'fixed';
            successMessage.style.top = '20px';
            successMessage.style.left = '50%';
            successMessage.style.transform = 'translateX(-50%)';
            successMessage.style.backgroundColor = '#4CAF50';
            successMessage.style.color = 'white';
            successMessage.style.padding = '12px 24px';
            successMessage.style.borderRadius = '5px';
            successMessage.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            successMessage.style.zIndex = '9999';
            successMessage.innerHTML = `<i class="fas fa-check-circle" style="margin-right: 8px;"></i>${message}`;
            
            document.body.appendChild(successMessage);
            
            // Remove the message after delay
            setTimeout(() => {
                successMessage.style.opacity = '0';
                successMessage.style.transition = 'opacity 0.5s ease';
                
                setTimeout(() => {
                    document.body.removeChild(successMessage);
                }, 500);
            }, 3000);
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal') || event.target.classList.contains('image-modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.getElementById('images-modal').style.display = 'none';
                document.getElementById('payment-modal').style.display = 'none';
                document.getElementById('details-modal').style.display = 'none';
            }
        });
    </script>
</body>
</html>