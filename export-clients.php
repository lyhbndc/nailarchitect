<?php
// Start session and database connection
session_start();
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all clients from the database
$query = "SELECT id, first_name, last_name, email, phone, is_verified, created_at 
          FROM users 
          ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Initialize statistics
$totalClients = 0;
$verifiedCount = 0;
$unverifiedCount = 0;
$newClientsThisMonth = 0;
$firstDayOfMonth = date('Y-m-01');

// Set up Excel headers
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Nail_Architect_Clients_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #F2E9E9;
        }
        
        .logo-container {
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #e0c5b7;
            position: relative;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #333;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .logo::after {
            content: "";
            position: absolute;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #dcdcdc;
            right: -15px;
            bottom: -15px;
            z-index: -1;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }
        
        .export-info {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .report-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
        }
        
        .statistics {
            margin: 20px 0;
            padding: 15px;
            background-color: #E8D7D0;
            border-radius: 10px;
            display: inline-block;
        }
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            min-width: 300px;
        }
        
        .stat-label {
            font-weight: bold;
            color: #555;
        }
        
        .stat-value {
            color: #333;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        
        th {
            background-color: #E8D7D0;
            color: #333;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #C1AAA0;
            font-size: 14px;
        }
        
        td {
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 13px;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #F2E9E9;
        }
        
        .status-verified {
            color: #2e7d32;
            font-weight: bold;
        }
        
        .status-unverified {
            color: #c62828;
            font-weight: bold;
        }
        
        .summary {
            margin-top: 40px;
            padding: 20px;
            background-color: #E8D7D0;
            border-radius: 10px;
        }
        
        .summary-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .summary-item {
            margin: 10px 0;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #C1AAA0;
        }
        
        .summary-label {
            font-weight: bold;
            color: #555;
        }
        
        .summary-value {
            color: #333;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-style: italic;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Header with Logo -->
    <div class="header">
        <div class="logo-container">
            <div class="logo">
                <span>♨</span>
            </div>
        </div>
        <div class="company-name">Nail Architect</div>
        <div class="export-info">Generated on: <?php echo date('F j, Y g:i A'); ?></div>
        <div class="report-title">Client Management Report</div>
    </div>
    
    <!-- Clients Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Client ID</th>
                <th style="width: 15%;">First Name</th>
                <th style="width: 15%;">Last Name</th>
                <th style="width: 20%;">Email</th>
                <th style="width: 12%;">Phone</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 12%;">Joined Date</th>
                <th style="width: 8%;">Appointments</th>
            </tr>
        </thead>
        <tbody>
            <?php
            mysqli_data_seek($result, 0); // Reset result pointer
            while ($row = mysqli_fetch_assoc($result)) {
                $totalClients++;
                $clientId = '#' . str_pad($row['id'], 4, '0', STR_PAD_LEFT);
                $status = $row['is_verified'] ? 'Verified' : 'Unverified';
                $statusClass = $row['is_verified'] ? 'status-verified' : 'status-unverified';
                $joinedDate = date('M d, Y', strtotime($row['created_at']));
                
                if ($row['is_verified']) {
                    $verifiedCount++;
                } else {
                    $unverifiedCount++;
                }
                
                if (strtotime($row['created_at']) >= strtotime($firstDayOfMonth)) {
                    $newClientsThisMonth++;
                }
                
                // Get appointment count for this client
                $appointmentQuery = "SELECT COUNT(*) as count FROM bookings WHERE user_id = {$row['id']}";
                $appointmentResult = mysqli_query($conn, $appointmentQuery);
                $appointmentCount = mysqli_fetch_assoc($appointmentResult)['count'];
                
                echo "<tr>
                        <td>$clientId</td>
                        <td>{$row['first_name']}</td>
                        <td>{$row['last_name']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['phone']}</td>
                        <td class='$statusClass'>$status</td>
                        <td>$joinedDate</td>
                        <td style='text-align: center;'>$appointmentCount</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
    
    <!-- Summary Section -->
    <div class="summary">
        <div class="summary-title">Client Statistics Summary</div>
        <div class="summary-item">
            <span class="summary-label">Total Clients:</span>
            <span class="summary-value"><?php echo $totalClients; ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Verified Clients:</span>
            <span class="summary-value" style="color: #2e7d32;"><?php echo $verifiedCount; ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Unverified Clients:</span>
            <span class="summary-value" style="color: #c62828;"><?php echo $unverifiedCount; ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">New Clients This Month:</span>
            <span class="summary-value"><?php echo $newClientsThisMonth; ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Verification Rate:</span>
            <span class="summary-value">
                <?php 
                $verificationRate = $totalClients > 0 ? 
                    round(($verifiedCount / $totalClients) * 100, 1) : 0;
                echo $verificationRate . '%';
                ?>
            </span>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        This report was generated from the Nail Architect client management system.<br>
        For internal use only. Contains confidential client information.
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>