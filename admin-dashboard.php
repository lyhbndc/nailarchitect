<?php
// Start session and database connection
session_start();
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get appointments with user information
$appointments_query = "SELECT b.*, u.first_name, u.last_name 
                       FROM bookings b 
                       LEFT JOIN users u ON b.user_id = u.id 
                       WHERE b.date >= CURDATE() 
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins;
        }
        
        body {
            background-color: #F2E9E9;
            padding: 20px;
        }
        
        .sidebar {
            width: 250px;
            background-color:#E8D7D0;
            height: 100vh;
            padding: 25px 0;
            position: fixed;
            overflow-y: auto;
            left: 0;
            top: 0;
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
            font-weight: bold;
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
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .menu-item:hover {
            background-color: #D9BBB0;
        }
        
        .menu-item.active {
            background-color: #D9BBB0;
        }
        
        .menu-item.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: #333;
        }
        
        .menu-icon {
            width: 20px;
            margin-right: 10px;
            text-align: center;
            font-size: 16px;
        }
        
        .menu-text {
            font-size: 14px;
        }
        
        .content-wrapper {
            margin-left: 250px;
            padding: 25px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #E8D7D0;
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .stat-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
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
        
        .content-section {
            background-color: #E8D7D0;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
        }
        
        .section-controls {
            display: flex;
            gap: 15px;
        }
        
        .control-button {
            padding: 8px 16px;
            border-radius: 20px;
            background-color: #D9BBB0;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .control-button:hover {
            background-color: #ae9389;
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
        }
        
        .tab.active {
            font-weight: bold;
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
        
        .appointments-table th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #c0c0c0;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
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
            font-weight: bold;
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
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
            background-color: #e0e0e0;
        }
        
        .action-button:hover {
            background-color: #c0c0c0;
        }
        
        .view-button {
            background-color: #bbdefb;
            color: #1565c0;
        }
        
        .edit-button {
            background-color: #fff9c4;
            color: #f57f17;
        }
        
        .cancel-button {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .image-button {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .details-panel {
            display: none;
            margin-top: 10px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 8px;
        }
        
        .details-panel-header {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .details-panel-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .details-panel-action {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #d9bbb0;
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
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: bold;
        }
        
        .close-modal {
            width: 30px;
            height: 30px;
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
            background-color: #c0c0c0;
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
        }
        
        .image-item img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            
            .content-wrapper {
                margin-left: 80px;
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
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo"></div>
            <div class="admin-title">Admin</div>
        </div>
        
        <div class="nav-menu">
            <div class="menu-section">MAIN</div>
            
            <div class="menu-item active">
                <div class="menu-icon">📊</div>
                <div class="menu-text">Dashboard</div>
            </div>
            
            <div class="menu-item">
                <div class="menu-icon">📅</div>
                <div class="menu-text">Appointments</div>
            </div>
            
            <div class="menu-item">
                <div class="menu-icon">👥</div>
                <div class="menu-text">Clients</div>
            </div>
            
            <div class="menu-item">
                <div class="menu-icon">💌</div>
                <div class="menu-text">Messages</div>
            </div>
            
            <div class="menu-section">SYSTEM</div>
            
            <div class="menu-item">
                <div class="menu-icon">↩️</div>
                <div class="menu-text">Logout</div>
            </div>
        </div>
    </div>
    
    <div class="content-wrapper">
        <div class="page-title">Dashboard</div>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-title">Total Appointments</div>
                <div class="stat-value"><?php echo $total_appointments; ?></div>
                <div class="stat-change change-positive">↑ 12% from last month</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Appointments Today</div>
                <div class="stat-value"><?php echo $appointments_today; ?></div>
                <div class="stat-change change-positive">↑ 5% from yesterday</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Pending Appointments</div>
                <div class="stat-value"><?php echo $pending_appointments; ?></div>
                <div class="stat-change change-negative">↑ 8% from last week</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Total Revenue</div>
                <div class="stat-value">$<?php echo number_format($total_revenue, 0); ?></div>
                <div class="stat-change change-positive">↑ 15% from last month</div>
            </div>
        </div>
        
        <div class="content-section">
            <div class="section-header">
                <div class="section-title">Upcoming Appointments</div>
                
                <div class="section-controls">
                    <div class="control-button">Export</div>
                    <div class="control-button">New Appointment</div>
                </div>
            </div>
            
            <div class="tabs">
                <div class="tab active" data-tab="all">All</div>
                <div class="tab" data-tab="confirmed">Confirmed</div>
                <div class="tab" data-tab="pending">Pending</div>
                <div class="tab" data-tab="cancelled">Cancelled</div>
            </div>
            
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
                    <?php if (mysqli_num_rows($appointments) > 0): ?>
                        <?php while ($appointment = mysqli_fetch_assoc($appointments)): 
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
                                        echo $appointment['first_name'] . ' ' . $appointment['last_name'];
                                    } else {
                                        echo $appointment['name']; 
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    // Format service name
                                    $service_name = ucfirst(str_replace('-', ' ', $appointment['service']));
                                    echo $service_name; 
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
                                    #NAI-<?php echo $appointment['reference_id']; ?>
                                </td>
                                <td class="action-cell">
                                    <div class="action-button view-button" title="View Details" data-id="<?php echo $appointment['id']; ?>">👁️</div>
                                    <div class="action-button edit-button" title="Edit">✏️</div>
                                    
                                    <?php if ($appointment['status'] != 'cancelled'): ?>
                                        <div class="action-button cancel-button" title="Cancel">✖️</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6" class="details-panel" id="details-<?php echo $appointment['id']; ?>">
                                    <div class="details-panel-header">
                                        Appointment Details for #NAI-<?php echo $appointment['reference_id']; ?>
                                    </div>
                                    <div>
                                        <strong>Notes:</strong> <?php echo $appointment['notes'] ? $appointment['notes'] : 'No notes provided'; ?>
                                    </div>
                                    <div class="details-panel-actions">
                                        <?php if ($has_images): ?>
                                            <div class="details-panel-action show-inspo" data-id="<?php echo $appointment['id']; ?>">View Inspiration Images</div>
                                        <?php endif; ?>
                                        
                                        <?php if ($has_payment): ?>
                                            <div class="details-panel-action show-payment" data-id="<?php echo $appointment['id']; ?>">View Payment Proof</div>
                                        <?php endif; ?>
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
                <div class="close-modal">&times;</div>
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
                <div class="close-modal">&times;</div>
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
            
            // Inspiration images modal functionality
            const inspoModal = document.getElementById('inspo-modal');
            const inspoGallery = document.getElementById('inspo-gallery');
            
            // Show inspiration images
            document.querySelectorAll('.show-inspo').forEach(button => {
                button.addEventListener('click', () => {
                    const appointmentId = button.getAttribute('data-id');
                    
                    // Clear gallery
                    inspoGallery.innerHTML = 'Loading...';
                    
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
                                    
                                    imageItem.appendChild(img);
                                    inspoGallery.appendChild(imageItem);
                                });
                            } else {
                                inspoGallery.innerHTML = '<p>No inspiration images found.</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            inspoGallery.innerHTML = '<p>Error loading images.</p>';
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
                    paymentGallery.innerHTML = 'Loading...';
                    
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
                                    
                                    imageItem.appendChild(img);
                                    paymentGallery.appendChild(imageItem);
                                });
                            } else {
                                paymentGallery.innerHTML = '<p>No payment proofs found.</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            paymentGallery.innerHTML = '<p>Error loading payment proofs.</p>';
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
        });
    </script>
</body>
</html>