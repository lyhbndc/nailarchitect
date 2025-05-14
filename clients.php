<?php
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all clients from the database
$query = "SELECT id, first_name, last_name, email, phone, is_verified, created_at 
          FROM users 
          ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);

// Calculate statistics
$totalClients = mysqli_num_rows($result);

// Count verified clients
$verifiedQuery = "SELECT COUNT(*) as count FROM users WHERE is_verified = 1";
$verifiedResult = mysqli_query($conn, $verifiedQuery);
$verifiedClients = mysqli_fetch_assoc($verifiedResult)['count'];

// Count new clients this month
$firstDayOfMonth = date('Y-m-01');
$newClientsQuery = "SELECT COUNT(*) as count FROM users WHERE created_at >= '$firstDayOfMonth'";
$newClientsResult = mysqli_query($conn, $newClientsQuery);
$newClients = mysqli_fetch_assoc($newClientsResult)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nail Architect - Client Management</title>
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
            grid-template-columns: repeat(3, 1fr);
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
            border: none;
            color: white;
        }
        
        .control-button:hover {
            background: linear-gradient(to right, #d98d8d, #ce7878);
        }
        
        .search-section {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #D9BBB0;
            border-radius: 8px;
            font-size: 14px;
            background-color: rgba(255, 255, 255, 0.5);
        }
        
        /* Fixed table styling with proper borders */
        .clients-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        
        .clients-table th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid rgb(177, 140, 140);
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        
        .clients-table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgb(196, 162, 162);
            font-size: 14px;
        }
        
        /* Fix for the last column (Actions) */
        .clients-table th:last-child,
        .clients-table td:last-child {
            text-align: center;
            width: 180px;
        }
        
        .clients-table tr:hover {
            background: linear-gradient(to right, rgb(233, 171, 171), rgb(226, 178, 178));
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
        
        .action-cell {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            min-height: 40px;
        }
        
        .action-button {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #2196f3;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
        }
        
        .action-button:hover {
            background-color: #1976d2;
            transform: translateY(-2px);
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
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
        <div class="page-title">Client Management</div>
    </div>
    
    <div class="content-wrapper">
        <!-- Statistics Section -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Clients</div>
                        <div class="stat-value"><?php echo $totalClients; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Verified Clients</div>
                        <div class="stat-value"><?php echo $verifiedClients; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">New This Month</div>
                        <div class="stat-value"><?php echo $newClients; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clients Table Section -->
        <div class="content-section">
            <div class="section-header">
                <div class="section-title">All Clients</div>
                <div class="section-controls">
                    <button class="control-button" id="refresh-btn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="control-button export-btn">
                        <i class="fas fa-file-export"></i> Export
                    </button>
                </div>
            </div>
            
            <div class="search-section">
                <input type="text" id="searchInput" class="search-input" 
                       placeholder="Search by name, email, or phone...">
                <button class="control-button" onclick="searchClients()">
                    <i class="fas fa-search"></i> Search
                </button>
                <button class="control-button" onclick="clearSearch()">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>

            <table class="clients-table">
                <thead>
                    <tr>
                        <th>CLIENT ID</th>
                        <th>NAME</th>
                        <th>EMAIL</th>
                        <th>PHONE</th>
                        <th>STATUS</th>
                        <th>JOINED DATE</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="clientsTableBody">
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $fullName = $row['first_name'] . ' ' . $row['last_name'];
                            $joinedDate = date('M d, Y', strtotime($row['created_at']));
                            $statusBadge = $row['is_verified'] ? 
                                '<span class="status-badge status-verified">Verified</span>' : 
                                '<span class="status-badge status-unverified">Unverified</span>';
                            
                            echo "<tr>
                                    <td>#" . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . "</td>
                                    <td>{$fullName}</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['phone']}</td>
                                    <td>{$statusBadge}</td>
                                    <td>{$joinedDate}</td>
                                    <td>
                                        <div class='action-cell'>
                                            <button class='action-button' 
                                                    onclick='viewClient({$row['id']})'>
                                                <i class='fas fa-eye'></i> View Details
                                            </button>
                                        </div>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='no-data'>No clients found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Search functionality
        function searchClients() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#clientsTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }

        // Clear search
        function clearSearch() {
            document.getElementById('searchInput').value = '';
            const rows = document.querySelectorAll('#clientsTableBody tr');
            rows.forEach(row => {
                row.style.display = '';
            });
        }

        // View client details
        function viewClient(clientId) {
            window.location.href = `client-details.php?id=${clientId}`;
        }

        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchClients();
            }
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
        
       // Export button functionality
        document.querySelector('.export-btn').addEventListener('click', () => {
        window.location.href = 'export-clients.php';
        });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>