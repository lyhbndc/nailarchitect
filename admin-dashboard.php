<?php
// Start session and database connection
session_start();
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}
// Get appointments with user information
$appointments_query = "SELECT b.*, u.first_name, u.last_name 
                       FROM bookings b 
                       LEFT JOIN users u ON b.user_id = u.id 
                       WHERE b.date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
                       ORDER BY b.date ASC, b.time ASC";
$appointments = mysqli_query($conn, $appointments_query);

// Get statistics
$total_appointments_query = "SELECT COUNT(*) as total FROM bookings";
$appointments_today_query = "SELECT COUNT(*) as today FROM bookings WHERE date = CURDATE()";
$pending_appointments_query = "SELECT COUNT(*) as pending FROM bookings WHERE status = 'pending'";
$total_revenue_query = "SELECT SUM(price) as revenue FROM bookings WHERE status != 'cancelled'";

$total_appointments = mysqli_query($conn, $total_appointments_query)->fetch_assoc()['total'];
$appointments_today = mysqli_query($conn, $appointments_today_query)->fetch_assoc()['today'];
$pending_appointments = mysqli_query($conn, $pending_appointments_query)->fetch_assoc()['pending'];
$total_revenue = mysqli_query($conn, $total_revenue_query)->fetch_assoc()['revenue'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nail Architect - Admin Dashboard</title>
    <link rel="stylesheet" href="sidebar-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
    
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(to right, rgb(237, 196, 196), rgb(226, 178, 178));
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.08);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background-color: rgba(255, 255, 255, 0.4);
        }
        
        .stat-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .stat-change {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .change-positive {
            color: #2e7d32;
        }
        
        .change-negative {
            color: #c62828;
        }
        
        .chart-container {
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(235, 184, 184, 0.3);
  box-shadow: 
    0 4px 16px rgba(0, 0, 0, 0.1),
    0 2px 8px rgba(0, 0, 0, 0.05),
    inset 0 1px 2px rgba(255, 255, 255, 0.3);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 16px;
            font-weight: 600;
        }
        
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .content-section {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            position: relative;
        }
        
        /* New scrollable appointments table styles */
        .appointments-wrapper {
            position: relative;
            max-height: 400px;
            overflow: hidden;
        }
        
        .appointments-wrapper.has-scroll::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: linear-gradient(to bottom, transparent, rgb(245, 207, 207));
            pointer-events: none;
            z-index: 1;
        }
        
        .appointments-scroll {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        /* Custom scrollbar styling */
        .appointments-scroll::-webkit-scrollbar {
            width: 8px;
        }
        
        .appointments-scroll::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 10px;
        }
        
        .appointments-scroll::-webkit-scrollbar-thumb {
            background: #d9bbb0;
            border-radius: 10px;
        }
        
        .appointments-scroll::-webkit-scrollbar-thumb:hover {
            background: #ae9389;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .section-controls {
            display: flex;
            gap: 15px;
        }
        
        .control-button {
            padding: 8px 16px;
            border-radius: 8px;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .control-button:hover {
            background: linear-gradient(to right, #d98d8d, #ce7878);
        }
        
        .tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
            border-bottom: 1px solid #c0c0c0;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            position: relative;
            font-weight: 500;
        }
        
        .tab.active {
            font-weight: 600;
        }
        
        .tab.active::after {
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #333;
        }
        
        .tab:hover {
            background-color: #D9BBB0;
        }
        
        .appointments-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .appointments-table thead {
            position: sticky;
            top: 0;
            background-color: rgb(245, 207, 207);
            z-index: 2;
        }
        
        .appointments-table th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #c0c0c0;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
            background-color: rgb(245, 207, 207);
        }
        
        .appointments-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        .appointments-table tr:hover {
            background-color: #D9BBB0;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
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
        
        .action-cell {
            display: flex;
            gap: 8px;
        }
        
        .action-button {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            background-color: rgba(255,255,255,0.5);
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            background-color: rgba(255,255,255,0.8);
        }
        
        .view-button {
            color: #1565c0;
        }
        
        .approve-button {
            color: #2e7d32;
        }
        
        .reject-button {
            color: #c62828;
        }
        
        .complete-button {
            color: #616161;
        }
        
        .details-panel {
            display: none;
            margin-top: 10px;
            padding: 15px;
            background-color: #F2E9E9;
            border-radius: 8px;
        }
        
        .details-panel-header {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .details-content {
            margin-top: 10px;
        }
        
        .details-panel-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .details-panel-action {
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #D9BBB0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .details-panel-action:hover {
            background-color: #ae9389;
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
            background-color: #f8f8f8;
            border-radius: 15px;
            padding: 30px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
        }
        
        .close-modal {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        
        .close-modal:hover {
            background-color: #D9BBB0;
        }
        
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .image-item {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .image-item:hover {
            transform: scale(1.02);
        }
        
        .image-item img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .quick-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .quick-stat {
            background-color: rgba(255, 255, 255, 0.3);
            padding: 12px 15px;
            border-radius: 8px;
            flex: 1;
            min-width: 120px;
        }
        
        .quick-stat-title {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .quick-stat-value {
            font-size: 18px;
            font-weight: 600;
        }
        
        /* Responsive Media Queries */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
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
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .top-bar {
                padding: 0 15px;
            }
            
            .content-wrapper {
                padding: 15px;
                padding-top: 70px;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .section-controls {
                width: 100%;
                justify-content: flex-end;
            }
            
            .appointments-wrapper,
            .appointments-scroll {
                max-height: 350px;
            }
            
            .appointments-table thead {
                position: static;
            }
        }
        
        @media (max-width: 576px) {
            .stat-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .image-gallery {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-spa" style="font-size: 22px; z-index: 1;"></i>
            </div>
            <div class="admin-title">Admin</div>
        </div>
        
        <div class="nav-menu">
    <div class="menu-section">MAIN</div>
    
    <div class="menu-item active" onclick="window.location.href='admin-dashboard.php'">
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
    
    <div class="menu-item" onclick="window.location.href='admin-messages.php'">
        <div class="menu-icon"><i class="fas fa-envelope"></i></div>
        <div class="menu-text">Messages</div>
    </div>
    <div class="menu-item" onclick="window.location.href='admin-inquiries.php'">
                <div class="menu-icon"><i class="fas fa-question-circle"></i></div>
                <div class="menu-text">Inquiries</div>
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
        <div class="page-title">Dashboard</div>
    </div>
    
    <div class="content-wrapper">
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Appointments</div>
                        <div class="stat-value"><?php echo $total_appointments; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Appointments Today</div>
                        <div class="stat-value"><?php echo $appointments_today; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Pending Appointments</div>
                        <div class="stat-value"><?php echo $pending_appointments; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
            
            <!-- <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Revenue</div>
                        <div class="stat-value">₱<?php echo number_format($total_revenue); ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div> -->
        </div>
        
        <div class="content-section">
            <div class="section-header">
                <div class="section-title">Upcoming Appointments</div>
                
                <div class="section-controls">
                    <div class="control-button" id="refresh-btn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </div>
                    <div class="control-button export-btn">
                        <i class="fas fa-file-export"></i> Export
                    </div>
                </div>
            </div>
            
            <div class="tabs">
                <div class="tab active" data-tab="all">All</div>
                <div class="tab" data-tab="confirmed">Confirmed</div>
                <div class="tab" data-tab="pending">Pending</div>
                <div class="tab" data-tab="cancelled">Cancelled</div>
            </div>
            
            <!-- SCROLLABLE WRAPPER -->
            <div class="appointments-wrapper <?php echo mysqli_num_rows($appointments) > 5 ? 'has-scroll' : ''; ?>">
                <div class="appointments-scroll">
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>CLIENT</th>
                                <th>SERVICE</th>
                                <th>DATE & TIME</th>
                                <th>STATUS</th>
                                <th>REFERENCE</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($appointments) > 0): 
                                mysqli_data_seek($appointments, 0); // Reset the result pointer
                                while ($appointment = mysqli_fetch_assoc($appointments)): 
                                    // Check if this appointment has any images
                                    $has_images_query = "SELECT COUNT(*) as count FROM booking_images WHERE booking_id = " . $appointment['id'];
                                    $has_images_result = mysqli_query($conn, $has_images_query);
                                    $has_images = $has_images_result->fetch_assoc()['count'] > 0;
                                    
                                    // Check if this appointment has payment proof
                                    $has_payment_query = "SELECT COUNT(*) as count FROM payment_proofs WHERE booking_id = " . $appointment['id'];
                                    $has_payment_result = mysqli_query($conn, $has_payment_query);
                                    $has_payment = $has_payment_result->fetch_assoc()['count'] > 0;
                            ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            if ($appointment['user_id']) {
                                                echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']);
                                            } else {
                                                echo htmlspecialchars($appointment['name']); 
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            // Format service name
                                            $service_name = ucfirst(str_replace('-', ' ', $appointment['service']));
                                            echo htmlspecialchars($service_name); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            echo date('M j, Y', strtotime($appointment['date'])) . ' - ';
                                            echo date('g:i A', strtotime($appointment['time'])); 
                                            ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            if (!empty($appointment['reference_id'])) {
                                                echo "#" . htmlspecialchars($appointment['reference_id']); 
                                            } else {
                                                echo "<span style='color: #c62828;'>No Ref</span>";
                                            }
                                            ?>
                                        </td>
                                        <td class="action-cell">
                                            <div class="action-button view-button" title="View Details" data-id="<?php echo $appointment['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </div>
                                            
                                            <?php if ($appointment['status'] == 'pending'): ?>
                                                <div class="action-button approve-button" title="Approve" data-id="<?php echo $appointment['id']; ?>">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                                <div class="action-button reject-button" title="Reject" data-id="<?php echo $appointment['id']; ?>">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                            <?php elseif ($appointment['status'] == 'confirmed'): ?>
                                                <div class="action-button complete-button" title="Mark as Completed" data-id="<?php echo $appointment['id']; ?>">
                                                    <i class="fas fa-check-double"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="details-panel" id="details-<?php echo $appointment['id']; ?>">
                                    <div class="details-panel-header">
                                        <div>Appointment #<?php echo htmlspecialchars($appointment['reference_id'] ?? 'N/A'); ?></div>
                                        <div class="details-close" style="cursor: pointer;" onclick="document.getElementById('details-<?php echo $appointment['id']; ?>').style.display='none'">
                                            <i class="fas fa-times"></i>
                                        </div>
                                    </div>
                                    <div class="details-content">
                                        <div class="quick-stats">
                                            <div class="quick-stat">
                                                <div class="quick-stat-title">Service</div>
                                                <div class="quick-stat-value"><?php echo ucfirst(str_replace('-', ' ', $appointment['service'])); ?></div>
                                            </div>
                                            
                                            <div class="quick-stat">
                                                <div class="quick-stat-title">Price</div>
                                                <div class="quick-stat-value">₱<?php echo number_format($appointment['price']); ?></div>
                                            </div>
                                            
                                            <div class="quick-stat">
                                                <div class="quick-stat-title">Phone</div>
                                                <div class="quick-stat-value"><?php echo htmlspecialchars($appointment['phone']); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div style="margin-top: 15px;">
                                            <strong>Notes:</strong> 
                                            <p style="margin-top: 5px; padding: 10px; background-color: rgba(255,255,255,0.3); border-radius: 8px;">
                                                <?php echo $appointment['notes'] ? htmlspecialchars($appointment['notes']) : 'No notes provided'; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="details-panel-actions">
                                        <?php if ($has_images): ?>
                                            <div class="details-panel-action show-inspo" data-id="<?php echo $appointment['id']; ?>">
                                                <i class="fas fa-images"></i> View Inspiration
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($has_payment): ?>
                                            <div class="details-panel-action show-payment" data-id="<?php echo $appointment['id']; ?>">
                                                <i class="fas fa-receipt"></i> View Payment
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="details-panel-action" onclick="window.location.href='send-reminder.php?id=<?php echo $appointment['id']; ?>'">
                                            <i class="fas fa-bell"></i> Send Reminder
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">No upcoming appointments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Inspiration Images Modal -->
    <div class="modal" id="inspo-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Inspiration Images</div>
                <div class="close-modal"><i class="fas fa-times"></i></div>
            </div>
            <div class="image-gallery" id="inspo-gallery">
                <!-- Images will be loaded here -->
            </div>
        </div>
    </div>
    
    <!-- Payment Proof Modal -->
    <div class="modal" id="payment-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Payment Proof</div>
                <div class="close-modal"><i class="fas fa-times"></i></div>
            </div>
            <div class="image-gallery" id="payment-gallery">
                <!-- Payment images will be loaded here -->
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabs = document.querySelectorAll('.tab');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    tab.classList.add('active');
                    
                    // Filter appointments by status
                    const status = tab.getAttribute('data-tab');
                    filterAppointmentsByStatus(status);
                });
            });
            
            // Function to filter appointments by status
            function filterAppointmentsByStatus(status) {
                const rows = document.querySelectorAll('.appointments-table tbody tr');
                
                rows.forEach(row => {
                    // Skip details panel rows
                    if (row.querySelector('.details-panel')) {
                        return;
                    }
                    
                    if (status === 'all') {
                        row.style.display = '';
                        // Also hide corresponding details panel
                        if (row.nextElementSibling && row.nextElementSibling.querySelector('.details-panel')) {
                            row.nextElementSibling.style.display = '';
                            // Make sure details panel is hidden
                            row.nextElementSibling.querySelector('.details-panel').style.display = 'none';
                        }
                    } else {
                        const statusBadge = row.querySelector('.status-badge');
                        if (statusBadge && statusBadge.textContent.trim().toLowerCase() === status) {
                            row.style.display = '';
                            // Also hide corresponding details panel
                            if (row.nextElementSibling && row.nextElementSibling.querySelector('.details-panel')) {
                                row.nextElementSibling.style.display = '';
                                // Make sure details panel is hidden
                                row.nextElementSibling.querySelector('.details-panel').style.display = 'none';
                            }
                        } else {
                            row.style.display = 'none';
                            // Also hide corresponding details panel
                            if (row.nextElementSibling && row.nextElementSibling.querySelector('.details-panel')) {
                                row.nextElementSibling.style.display = 'none';
                            }
                        }
                    }
                });
            }
            
            // View details functionality
            document.querySelectorAll('.view-button').forEach(button => {
                button.addEventListener('click', () => {
                    const appointmentId = button.getAttribute('data-id');
                    const detailsPanel = document.getElementById('details-' + appointmentId);
                    
                    // Toggle details panel
                    if (detailsPanel.style.display === 'block') {
                        detailsPanel.style.display = 'none';
                    } else {
                        // Hide all other details panels first
                        document.querySelectorAll('.details-panel').forEach(panel => {
                            panel.style.display = 'none';
                        });
                        
                        // Show this details panel
                        detailsPanel.style.display = 'block';
                    }
                });
            });
            
            // Approve button functionality
            document.querySelectorAll('.approve-button').forEach(button => {
                button.addEventListener('click', () => {
                    if (confirm('Are you sure you want to approve this appointment?')) {
                        const appointmentId = button.getAttribute('data-id');
                        updateAppointmentStatus(appointmentId, 'confirmed');
                    }
                });
            });

            // Reject button functionality
            document.querySelectorAll('.reject-button').forEach(button => {
                button.addEventListener('click', () => {
                    if (confirm('Are you sure you want to reject this appointment?')) {
                        const appointmentId = button.getAttribute('data-id');
                        updateAppointmentStatus(appointmentId, 'cancelled');
                    }
                });
            });

            // Complete button functionality
            document.querySelectorAll('.complete-button').forEach(button => {
                button.addEventListener('click', () => {
                    if (confirm('Are you sure you want to mark this appointment as completed?')) {
                        const appointmentId = button.getAttribute('data-id');
                        updateAppointmentStatus(appointmentId, 'completed');
                    }
                });
            });

            // Function to update appointment status
            function updateAppointmentStatus(id, status) {
                // Create form data
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', status);
                
                // Show loading state
                const loadingOverlay = document.createElement('div');
                loadingOverlay.style.position = 'fixed';
                loadingOverlay.style.top = '0';
                loadingOverlay.style.left = '0';
                loadingOverlay.style.width = '100%';
                loadingOverlay.style.height = '100%';
                loadingOverlay.style.backgroundColor = 'rgba(0,0,0,0.3)';
                loadingOverlay.style.display = 'flex';
                loadingOverlay.style.alignItems = 'center';
                loadingOverlay.style.justifyContent = 'center';
                loadingOverlay.style.zIndex = '9999';
                loadingOverlay.innerHTML = '<div style="background: white; padding: 20px; border-radius: 10px;"><i class="fas fa-spinner fa-spin" style="margin-right: 10px;"></i> Updating...</div>';
                document.body.appendChild(loadingOverlay);
                
                // Send AJAX request
                fetch('update-appointment-status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Remove loading overlay
                    document.body.removeChild(loadingOverlay);
                    
                    if (data.success) {
                        // Show success message
                        const successMessage = document.createElement('div');
                        successMessage.style.position = 'fixed';
                        successMessage.style.top = '20px';
                        successMessage.style.left = '50%';
                        successMessage.style.transform = 'translateX(-50%)';
                        successMessage.style.padding = '15px 25px';
                        successMessage.style.backgroundColor = '#4caf50';
                        successMessage.style.color = 'white';
                        successMessage.style.borderRadius = '5px';
                        successMessage.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                        successMessage.style.zIndex = '9999';
                        successMessage.innerHTML = '<i class="fas fa-check-circle" style="margin-right: 10px;"></i> Appointment status updated successfully!';
                        document.body.appendChild(successMessage);
                        
                        // Remove message after 3 seconds and reload page
                        setTimeout(() => {
                            document.body.removeChild(successMessage);
                            window.location.reload();
                        }, 2000);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    // Remove loading overlay
                    document.body.removeChild(loadingOverlay);
                    
                    console.error('Error:', error);
                    alert('An error occurred while updating the appointment status.');
                });
            }

            // Export button functionality
            document.querySelector('.export-btn').addEventListener('click', () => {
                // Get the active tab to use as filter
                const activeTab = document.querySelector('.tab.active');
                const status = activeTab.getAttribute('data-tab');
                
                // Redirect to export script with filter
                window.location.href = 'export-appointments.php?status=' + status;
            });
            
            // Refresh button functionality
            document.getElementById('refresh-btn').addEventListener('click', () => {
                const refreshBtn = document.getElementById('refresh-btn');
                refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
                refreshBtn.style.pointerEvents = 'none';
                
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            });
            
            // Inspiration images modal functionality
            const inspoModal = document.getElementById('inspo-modal');
            const inspoGallery = document.getElementById('inspo-gallery');
            
            // Show inspiration images
            document.querySelectorAll('.show-inspo').forEach(button => {
                button.addEventListener('click', () => {
                    const appointmentId = button.getAttribute('data-id');
                    
                    // Clear gallery
                    inspoGallery.innerHTML = '<div style="text-align: center; width: 100%;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
                    
                    // Show modal
                    inspoModal.style.display = 'flex';
                    
                    // Fetch images
                    fetch('get-inspiration-images.php?id=' + appointmentId)
                        .then(response => response.json())
                        .then(data => {
                            inspoGallery.innerHTML = '';
                            
                            if (data.success && data.images.length > 0) {
                                data.images.forEach(image => {
                                    const imageItem = document.createElement('div');
                                    imageItem.className = 'image-item';
                                    
                                    const img = document.createElement('img');
                                    img.src = image;
                                    img.alt = 'Inspiration Image';
                                    img.loading = 'lazy'; // Lazy loading for better performance
                                    
                                    imageItem.appendChild(img);
                                    inspoGallery.appendChild(imageItem);
                                });
                            } else {
                                inspoGallery.innerHTML = '<p style="text-align: center; width: 100%;">No inspiration images found.</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            inspoGallery.innerHTML = '<p style="text-align: center; width: 100%;">Error loading images.</p>';
                        });
                });
            });
            
            // Payment proof modal functionality
            const paymentModal = document.getElementById('payment-modal');
            const paymentGallery = document.getElementById('payment-gallery');
            
            // Show payment proofs
            document.querySelectorAll('.show-payment').forEach(button => {
                button.addEventListener('click', () => {
                    const appointmentId = button.getAttribute('data-id');
                    
                    // Clear gallery
                    paymentGallery.innerHTML = '<div style="text-align: center; width: 100%;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
                    
                    // Show modal
                    paymentModal.style.display = 'flex';
                    
                    // Fetch payment proofs
                    fetch('get-payment-proofs.php?id=' + appointmentId)
                        .then(response => response.json())
                        .then(data => {
                            paymentGallery.innerHTML = '';
                            
                            if (data.success && data.images.length > 0) {
                                data.images.forEach(image => {
                                    const imageItem = document.createElement('div');
                                    imageItem.className = 'image-item';
                                    
                                    const img = document.createElement('img');
                                    img.src = image;
                                    img.alt = 'Payment Proof';
                                    img.loading = 'lazy'; // Lazy loading for better performance
                                    
                                    imageItem.appendChild(img);
                                    paymentGallery.appendChild(imageItem);
                                });
                            } else {
                                paymentGallery.innerHTML = '<p style="text-align: center; width: 100%;">No payment proofs found.</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            paymentGallery.innerHTML = '<p style="text-align: center; width: 100%;">Error loading payment proofs.</p>';
                        });
                });
            });
            
            // Close modals
            document.querySelectorAll('.close-modal').forEach(button => {
                button.addEventListener('click', () => {
                    inspoModal.style.display = 'none';
                    paymentModal.style.display = 'none';
                });
            });
            
            // Close modals when clicking outside
            window.addEventListener('click', event => {
                if (event.target === inspoModal) {
                    inspoModal.style.display = 'none';
                }
                if (event.target === paymentModal) {
                    paymentModal.style.display = 'none';
                }
            });
            
            // Add keyboard shortcut (Escape key) to close modals
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    inspoModal.style.display = 'none';
                    paymentModal.style.display = 'none';
                    
                    // Close any open details panels
                    document.querySelectorAll('.details-panel').forEach(panel => {
                        panel.style.display = 'none';
                    });
                }
            });
        });
    </script>
</body>
</html>