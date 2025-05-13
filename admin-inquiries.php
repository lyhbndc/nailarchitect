<?php
// admin-inquiries.php - Complete Admin Inquiry Management System
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle status update via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $inquiry_id = mysqli_real_escape_string($conn, $_POST['inquiry_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE inquiries SET status = '$status' WHERE id = '$inquiry_id'";
    mysqli_query($conn, $update_query);
    
    echo json_encode(['success' => true]);
    exit();
}

// Handle contact form submission (from client side)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    // Sanitize input data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // Split name into first and last name
    $name_parts = explode(' ', $name, 2);
    $first_name = $name_parts[0];
    $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
    
    // Insert inquiry into database
    $query = "INSERT INTO inquiries (first_name, last_name, email, phone, subject, message, status, created_at) 
              VALUES ('$first_name', '$last_name', '$email', '$phone', '$subject', '$message', 'unread', NOW())";
    
    if (mysqli_query($conn, $query)) {
        // Redirect to contact success page or show success message
        header("Location: contact.php?success=1");
        exit();
    } else {
        // Handle error
        header("Location: contact.php?error=1");
        exit();
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $inquiry_id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Delete inquiry
    $delete_inquiry = "DELETE FROM inquiries WHERE id = '$inquiry_id'";
    mysqli_query($conn, $delete_inquiry);
    
    header("Location: admin-inquiries.php");
    exit();
}

// Get filter and pagination
$filterStatus = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$searchQuery = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build query with filters
$whereClause = "WHERE 1=1";
if ($filterStatus !== 'all') {
    $whereClause .= " AND status = '$filterStatus'";
}
if (!empty($searchQuery)) {
    $whereClause .= " AND (first_name LIKE '%$searchQuery%' OR last_name LIKE '%$searchQuery%' OR email LIKE '%$searchQuery%' OR subject LIKE '%$searchQuery%')";
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM inquiries $whereClause";
$countResult = mysqli_query($conn, $countQuery);
$totalCount = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalCount / $perPage);

// Get inquiries
$inquiries_query = "SELECT * FROM inquiries $whereClause ORDER BY created_at DESC LIMIT $offset, $perPage";
$inquiries_result = mysqli_query($conn, $inquiries_query);

// Get stats
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'unread' THEN 1 ELSE 0 END) as unread_count,
    SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
    SUM(CASE WHEN status = 'responded' THEN 1 ELSE 0 END) as responded_count
    FROM inquiries";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nail Architect - Admin Inquiries</title>
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
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.total {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .stat-icon.unread {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .stat-icon.read {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .stat-icon.responded {
            background-color: #d4edda;
            color: #155724;
        }
        
        .stat-details h3 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .stat-details p {
            font-size: 14px;
            color: #666;
        }
        
        .inquiries-table-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .search-filter {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .search-box {
            padding: 10px 15px;
            border: 1px solid #e0c5b7;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            width: 300px;
        }
        
        .filter-buttons {
            display: flex;
            gap: 8px;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: 1px solid #e0c5b7;
            background-color: white;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .filter-btn.active {
            background: linear-gradient(to right, #d98d8d, #e6a4a4);
            color: white;
            border-color: transparent;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background-color: #f8f9fa;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 14px;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        tbody tr:hover {
            background-color: #f9f2f2;
        }
        
        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .customer-name {
            font-weight: 500;
            color: #333;
        }
        
        .customer-email {
            font-size: 13px;
            color: #666;
        }
        
        .subject {
            font-weight: 500;
            color: #333;
            display: block;
        }
        
        .message-preview {
            font-size: 13px;
            color: #666;
            display: block;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-badge.unread {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-badge.read {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-badge.responded {
            background-color: #d4edda;
            color: #155724;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        .action-btn.view {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .action-btn.view:hover {
            background-color: #bbdefb;
        }
        
        .action-btn.email {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .action-btn.email:hover {
            background-color: #c8e6c9;
        }
        
        .action-btn.delete {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .action-btn.delete:hover {
            background-color: #ffcdd2;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 20px;
        }
        
        .page-link {
            padding: 8px 12px;
            border: 1px solid #e0c5b7;
            background-color: white;
            border-radius: 8px;
            text-decoration: none;
            color: #666;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .page-link.active {
            background: linear-gradient(to right, #d98d8d, #e6a4a4);
            color: white;
            border-color: transparent;
        }
        
        .page-link:hover:not(.active) {
            border-color: #e6a4a4;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            padding: 20px;
        }
        
        .modal-content {
            background-color: white;
            max-width: 600px;
            margin: 50px auto;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 50px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            padding: 20px;
            background: linear-gradient(to right, rgb(222, 131, 131), rgb(111, 33, 50));
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 500;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .inquiry-detail {
            margin-bottom: 20px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .detail-value {
            font-size: 14px;
            color: #333;
            margin-bottom: 15px;
        }
        
        .message-box {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #e6a4a4;
            white-space: pre-wrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
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
            
            <div class="menu-item" onclick="window.location.href='clients.php'">
                <div class="menu-icon"><i class="fas fa-users"></i></div>
                <div class="menu-text">Clients</div>
            </div>
            
            <div class="menu-item" onclick="window.location.href='admin-messages.php'">
                <div class="menu-icon"><i class="fas fa-envelope"></i></div>
                <div class="menu-text">Messages</div>
            </div>
            
            <div class="menu-item active" onclick="window.location.href='admin-inquiries.php'">
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
        <div class="page-title">Customer Inquiries</div>
    </div>
    
    <div class="content-wrapper">
        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Inquiries</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon unread">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['unread_count']; ?></h3>
                    <p>Unread</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon read">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['read_count']; ?></h3>
                    <p>Read</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon responded">
                    <i class="fas fa-reply"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['responded_count']; ?></h3>
                    <p>Responded</p>
                </div>
            </div>
        </div>
        
        <!-- Inquiries Table -->
        <div class="inquiries-table-container">
            <div class="table-header">
                <div class="search-filter">
                    <input type="text" class="search-box" placeholder="Search inquiries..." id="searchInput" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    
                    <div class="filter-buttons">
                        <a href="?filter=all" class="filter-btn <?php echo $filterStatus === 'all' ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> All
                        </a>
                        <a href="?filter=unread" class="filter-btn <?php echo $filterStatus === 'unread' ? 'active' : ''; ?>">
                            <i class="fas fa-envelope"></i> Unread
                        </a>
                        <a href="?filter=read" class="filter-btn <?php echo $filterStatus === 'read' ? 'active' : ''; ?>">
                            <i class="fas fa-envelope-open"></i> Read
                        </a>
                        <a href="?filter=responded" class="filter-btn <?php echo $filterStatus === 'responded' ? 'active' : ''; ?>">
                            <i class="fas fa-reply"></i> Responded
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if (mysqli_num_rows($inquiries_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($inquiry = mysqli_fetch_assoc($inquiries_result)): ?>
                            <?php
                            $inquiry_date = new DateTime($inquiry['created_at']);
                            $formatted_date = $inquiry_date->format('M j, Y g:i A');
                            ?>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name"><?php echo htmlspecialchars($inquiry['first_name'] . ' ' . $inquiry['last_name']); ?></span>
                                        <span class="customer-email"><?php echo htmlspecialchars($inquiry['email']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="subject"><?php echo htmlspecialchars($inquiry['subject']); ?></span>
                                </td>
                                <td>
                                    <span class="message-preview"><?php echo htmlspecialchars(substr($inquiry['message'], 0, 100)) . (strlen($inquiry['message']) > 100 ? '...' : ''); ?></span>
                                </td>
                                <td><?php echo $formatted_date; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $inquiry['status']; ?>">
                                        <?php echo ucfirst($inquiry['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn view" onclick="viewInquiry(<?php echo $inquiry['id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <a href="mailto:<?php echo $inquiry['email']; ?>?subject=Re: <?php echo urlencode($inquiry['subject']); ?>&body=Dear <?php echo urlencode($inquiry['first_name']); ?>,%0A%0AThank you for your inquiry about <?php echo urlencode($inquiry['subject']); ?>.%0A%0A" 
                                           class="action-btn email" 
                                           target="_blank"
                                           onclick="markAsResponded(<?php echo $inquiry['id']; ?>)">
                                            <i class="fas fa-envelope"></i> Reply
                                        </a>
                                        <a href="?delete=<?php echo $inquiry['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this inquiry?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&filter=<?php echo $filterStatus; ?>&search=<?php echo urlencode($searchQuery); ?>" 
                               class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                    <h3>No inquiries found</h3>
                    <p>There are no inquiries matching your current filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- View Inquiry Modal -->
    <div id="inquiryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Inquiry Details</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', debounce(function() {
            const searchValue = this.value;
            window.location.href = `?filter=<?php echo $filterStatus; ?>&search=${encodeURIComponent(searchValue)}`;
        }, 500));
        
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
// Update status via AJAX
function updateStatus(inquiryId, status) {
    fetch('admin-inquiries.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `update_status=1&inquiry_id=${inquiryId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the status badge in the table without reloading
            const statusBadge = document.querySelector(`tr:has(button[onclick*="${inquiryId}"]) .status-badge`);
            if (statusBadge) {
                statusBadge.className = `status-badge ${status}`;
                statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
        
function viewInquiry(inquiryId) {
    const modal = document.getElementById('inquiryModal');
    const modalBody = document.getElementById('modalBody');
    
    // Show loading
    modalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #e6a4a4;"></i></div>';
    modal.style.display = 'block';
    
    // Mark as read
    updateStatus(inquiryId, 'read');
    
    // Fetch inquiry details
    fetch(`get-inquiry-details.php?id=${inquiryId}`)
        .then(response => response.text())
        .then(html => {
            modalBody.innerHTML = html;
            // REMOVE THIS PART - it's causing the modal to close
            // setTimeout(() => {
            //     location.reload();
            // }, 500);
        })
        .catch(error => {
            modalBody.innerHTML = '<div style="text-align: center; color: #dc3545;">Failed to load inquiry details.</div>';
        });
}
        
        // Mark as responded when replying
        function markAsResponded(inquiryId) {
            updateStatus(inquiryId, 'responded');
        }
        
        // Close modal
        function closeModal() {
            document.getElementById('inquiryModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('inquiryModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>