<?php
session_start();

// Check if client ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: clients.php');
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$clientId = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch client details
$clientQuery = "SELECT * FROM users WHERE id = '$clientId'";
$clientResult = mysqli_query($conn, $clientQuery);

if (mysqli_num_rows($clientResult) == 0) {
    header('Location: clients.php');
    exit();
}

$client = mysqli_fetch_assoc($clientResult);

// Fetch client's booking history
$bookingsQuery = "SELECT b.*, GROUP_CONCAT(bi.image_path) as inspiration_images,
                  pp.image_path as payment_proof
                  FROM bookings b
                  LEFT JOIN booking_images bi ON b.id = bi.booking_id
                  LEFT JOIN payment_proofs pp ON b.id = pp.booking_id
                  WHERE b.user_id = '$clientId'
                  GROUP BY b.id
                  ORDER BY b.created_at DESC";
$bookingsResult = mysqli_query($conn, $bookingsQuery);

// Fetch client's messages
$messagesQuery = "SELECT m.*, ma.file_name
                  FROM messages m
                  LEFT JOIN message_attachments ma ON m.id = ma.message_id
                  WHERE m.user_id = '$clientId'
                  ORDER BY m.created_at DESC
                  LIMIT 10";
$messagesResult = mysqli_query($conn, $messagesQuery);

// Calculate client statistics
$totalBookings = mysqli_num_rows($bookingsResult);
$completedBookings = 0;
$totalSpent = 0;

// Reset pointer to calculate statistics
mysqli_data_seek($bookingsResult, 0);
while ($booking = mysqli_fetch_assoc($bookingsResult)) {
    if ($booking['status'] == 'completed') {
        $completedBookings++;
        $totalSpent += $booking['price'];
    }
}
mysqli_data_seek($bookingsResult, 0); // Reset pointer again for display
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Details - <?php echo $client['first_name'] . ' ' . $client['last_name']; ?></title>
    <link rel="stylesheet" href="sidebar-admin.css">
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

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
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
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            border: none;
        }
        
        .btn-back:hover {
            background: linear-gradient(to right, #d98d8d, #ce7878);
        }
        
        .client-header {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .client-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 600;
        }
        
        .client-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-weight: 500;
            font-size: 16px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-verified {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .status-unverified {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .status-pending {
            background-color: #fff9c4;
            color: #f57f17;
        }
        
        .status-confirmed {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background-color: #e0e0e0;
            color: #616161;
        }
        
        .status-cancelled {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .content-section {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
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
        
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .bookings-table th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid rgb(177, 140, 140);
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        
        .bookings-table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgb(196, 162, 162);
            font-size: 14px;
        }
        
        .bookings-table tr:hover {
            background: linear-gradient(to right, rgb(233, 171, 171), rgb(226, 178, 178));
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .action-button {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #2196f3;
            color: white;
            display: flex;
            align-items: center;
            gap: 5px;
            border: none;
        }
        
        .action-button:hover {
            background-color: #1976d2;
            transform: translateY(-2px);
        }
        
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
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
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .client-info {
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
            
            <div class="menu-item active" onclick="window.location.href='clients.php'">
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
        <div class="page-title">Client Details</div>
    </div>
    
    <div class="content-wrapper">
        <a href="clients.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Client List
        </a>

        <!-- Client Header -->
        <div class="client-header">
            <h1><?php echo $client['first_name'] . ' ' . $client['last_name']; ?></h1>
            <?php
            $statusBadge = $client['is_verified'] ? 
                '<span class="status-badge status-verified">Verified</span>' : 
                '<span class="status-badge status-unverified">Unverified</span>';
            echo $statusBadge;
            ?>
            
            <div class="client-info">
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo $client['email']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone</span>
                    <span class="info-value"><?php echo $client['phone']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Member Since</span>
                    <span class="info-value"><?php echo date('F d, Y', strtotime($client['created_at'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Client ID</span>
                    <span class="info-value">#<?php echo str_pad($client['id'], 4, '0', STR_PAD_LEFT); ?></span>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Bookings</div>
                        <div class="stat-value"><?php echo $totalBookings; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Completed Bookings</div>
                        <div class="stat-value"><?php echo $completedBookings; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking History -->
        <div class="content-section">
            <div class="section-header">
                <div class="section-title">Booking History</div>
            </div>
            
            <?php if (mysqli_num_rows($bookingsResult) > 0): ?>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>REFERENCE ID</th>
                            <th>SERVICE</th>
                            <th>DATE</th>
                            <th>TIME</th>
                            <th>STATUS</th>
                            <th>PRICE</th>
                            <th>NOTES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = mysqli_fetch_assoc($bookingsResult)): ?>
                            <tr>
                                <td>#<?php echo $booking['reference_id']; ?></td>
                                <td><?php echo ucwords(str_replace('-', ' ', $booking['service'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($booking['time'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>₱<?php echo number_format($booking['price'], 2); ?></td>
                                <td><?php echo $booking['notes'] ?: '-'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">No bookings found for this client.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>