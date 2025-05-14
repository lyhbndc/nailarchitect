<?php
// Start session and database connection
session_start();
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Get current admin's role
$current_admin_id = $_SESSION['admin_id'];
$role_query = "SELECT role FROM admin_users WHERE id = ?";
$stmt = mysqli_prepare($conn, $role_query);
mysqli_stmt_bind_param($stmt, "i", $current_admin_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$current_admin = mysqli_fetch_assoc($result);
$is_super_admin = ($current_admin['role'] === 'super_admin');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Only super admin can create admins
        if ($action == 'create' && $is_super_admin) {
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $password = $_POST['password'];
            $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
            $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
            $phone = mysqli_real_escape_string($conn, $_POST['phone']);
            $role = mysqli_real_escape_string($conn, $_POST['role']);
            
            // Validate password
            if (empty($password)) {
                $error_message = "Password cannot be empty!";
            } elseif (strlen($password) < 6) {
                $error_message = "Password must be at least 6 characters long!";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Check if trying to create super_admin and one already exists
                if ($role === 'super_admin') {
                    $check_super = "SELECT COUNT(*) as count FROM admin_users WHERE role = 'super_admin'";
                    $super_result = mysqli_query($conn, $check_super);
                    $super_count = mysqli_fetch_assoc($super_result)['count'];
                    
                    if ($super_count > 0) {
                        $error_message = "Only one super admin is allowed in the system!";
                    } else {
                        $query = "INSERT INTO admin_users (username, email, password, first_name, last_name, phone, role) 
                                  VALUES ('$username', '$email', '$hashed_password', '$first_name', '$last_name', '$phone', '$role')";
                        
                        if (mysqli_query($conn, $query)) {
                            $success_message = "Admin user created successfully!";
                        } else {
                            $error_message = "Error creating admin user: " . mysqli_error($conn);
                        }
                    }
                } else {
                    $query = "INSERT INTO admin_users (username, email, password, first_name, last_name, phone, role) 
                              VALUES ('$username', '$email', '$hashed_password', '$first_name', '$last_name', '$phone', '$role')";
                    
                    if (mysqli_query($conn, $query)) {
                        $success_message = "Admin user created successfully!";
                    } else {
                        $error_message = "Error creating admin user: " . mysqli_error($conn);
                    }
                }
            }
        }
        
        // Allow admins to update their own profile, or super admin to update anyone
        if ($action == 'update' && ($is_super_admin || $_POST['admin_id'] == $current_admin_id)) {
            $admin_id = $_POST['admin_id'];
            $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
            $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $phone = mysqli_real_escape_string($conn, $_POST['phone']);
            
            // Only super admin can change roles and status
            if ($is_super_admin) {
                $role = mysqli_real_escape_string($conn, $_POST['role']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Prevent changing own role from super_admin
                if ($admin_id == $current_admin_id && $current_admin['role'] === 'super_admin' && $role !== 'super_admin') {
                    $error_message = "Cannot change your own super admin role!";
                } else {
                    // Check if trying to create another super_admin
                    if ($role === 'super_admin') {
                        $check_super = "SELECT COUNT(*) as count FROM admin_users WHERE role = 'super_admin' AND id != $admin_id";
                        $super_result = mysqli_query($conn, $check_super);
                        $super_count = mysqli_fetch_assoc($super_result)['count'];
                        
                        if ($super_count > 0) {
                            $error_message = "Only one super admin is allowed in the system!";
                        } else {
                            $update_allowed = true;
                        }
                    } else {
                        $update_allowed = true;
                    }
                }
            } else {
                // Regular admins keep their current role and status
                $role_status_query = "SELECT role, is_active FROM admin_users WHERE id = ?";
                $stmt = mysqli_prepare($conn, $role_status_query);
                mysqli_stmt_bind_param($stmt, "i", $admin_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $admin_data = mysqli_fetch_assoc($result);
                $role = $admin_data['role'];
                $is_active = $admin_data['is_active'];
                $update_allowed = true;
            }
                
            if (isset($update_allowed) && $update_allowed) {
                $query = "UPDATE admin_users SET 
                          first_name = '$first_name',
                          last_name = '$last_name',
                          email = '$email',
                          phone = '$phone',
                          role = '$role',
                          is_active = $is_active
                          WHERE id = $admin_id";
                
                if (!empty($_POST['password'])) {
                    $password = $_POST['password'];
                    if (strlen($password) < 6) {
                        $error_message = "Password must be at least 6 characters long!";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $query = "UPDATE admin_users SET 
                                  first_name = '$first_name',
                                  last_name = '$last_name',
                                  email = '$email',
                                  phone = '$phone',
                                  role = '$role',
                                  is_active = $is_active,
                                  password = '$hashed_password'
                                  WHERE id = $admin_id";
                    }
                }
                
                if (!isset($error_message) && mysqli_query($conn, $query)) {
                    $success_message = "Admin user updated successfully!";
                } elseif (!isset($error_message)) {
                    $error_message = "Error updating admin user: " . mysqli_error($conn);
                }
            }
        }
        
        // Only super admin can delete admins
        if ($action == 'delete' && $is_super_admin) {
            $admin_id = $_POST['admin_id'];
            
            // Prevent deleting yourself
            if ($admin_id == $current_admin_id) {
                $error_message = "You cannot delete your own account!";
            } else {
                $query = "DELETE FROM admin_users WHERE id = $admin_id";
                
                if (mysqli_query($conn, $query)) {
                    $success_message = "Admin user deleted successfully!";
                } else {
                    $error_message = "Error deleting admin user: " . mysqli_error($conn);
                }
            }
        }
        
        // Show error if non-super admin tries forbidden actions
        if (!$is_super_admin && in_array($action, ['create', 'delete'])) {
            $error_message = "Only super admins can manage admin users!";
        }
    }
}

// Get all admin users
$admins_query = "SELECT * FROM admin_users ORDER BY created_at DESC";
$admins_result = mysqli_query($conn, $admins_query);

// Get statistics
$total_admins = mysqli_num_rows($admins_result);
$active_admins_query = "SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1";
$active_admins = mysqli_query($conn, $active_admins_query)->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nail Architect - Admin Management</title>
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
            background: linear-gradient(135deg, #F5D0D0 0%, #F0B8B8 100%);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
        }
        
        .stat-card-content {
            position: relative;
            z-index: 1;
        }
        
        .stat-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            line-height: 1;
        }
        
        .stat-icon {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 40px;
            color: rgba(0, 0, 0, 0.1);
        }
        
        .content-section {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }
        
        .control-button {
            padding: 10px 20px;
            border-radius: 8px;
            background: linear-gradient(135deg, #E6A4A4 0%, #D98D8D 100%);
            color: white;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .control-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(217, 141, 141, 0.4);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th {
            text-align: left;
            padding: 15px;
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            border-bottom: 2px solid #F5F5F5;
        }
        
        .admin-table td {
            padding: 15px;
            border-bottom: 1px solid #F5F5F5;
            vertical-align: middle;
        }
        
        .admin-table tr:hover {
            background-color: #FAFAFA;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #E6A4A4 0%, #D98D8D 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
            color: white;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .user-username {
            font-size: 12px;
            color: #999;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-active {
            background-color: #E8F5E9;
            color: #4CAF50;
        }
        
        .status-inactive {
            background-color: #FFEBEE;
            color: #F44336;
        }
        
        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            background-color: #FFF3E0;
            color: #FF9800;
        }
        
        .role-super_admin {
            background-color: #FFFDE7;
            color: #FBC02D;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .action-button {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 16px;
            border: none;
            background-color: #F5F5F5;
        }
        
        .action-button:hover {
            background-color: #E0E0E0;
        }
        
        .edit-button {
            color: #2196F3;
        }
        
        .delete-button {
            color: #F44336;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .modal-title {
            font-size: 22px;
            font-weight: 600;
            color: #333;
        }
        
        .close-modal {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #F5F5F5;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 20px;
            color: #666;
        }
        
        .close-modal:hover {
            background-color: #E0E0E0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #E6A4A4;
            box-shadow: 0 0 0 3px rgba(230, 164, 164, 0.1);
        }
        
        .password-input-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 18px;
        }
        
        .password-toggle:hover {
            color: #333;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .submit-button {
            flex: 1;
            padding: 12px 20px;
            background: linear-gradient(135deg, #E6A4A4 0%, #D98D8D 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(217, 141, 141, 0.4);
        }
        
        .cancel-button {
            flex: 1;
            padding: 12px 20px;
            background-color: #F5F5F5;
            color: #666;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .cancel-button:hover {
            background-color: #E0E0E0;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: #E8F5E9;
            color: #2E7D32;
            border: 1px solid #C8E6C9;
        }
        
        .alert-error {
            background-color: #FFEBEE;
            color: #C62828;
            border: 1px solid #FFCDD2;
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

            <div class="menu-item active" onclick="window.location.href='admin-management.php'">
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
            
            <div class="menu-item" onclick="window.location.href='admin-logout.php'">
                <div class="menu-icon"><i class="fas fa-sign-out-alt"></i></div>
                <div class="menu-text">Logout</div>
            </div>
        </div>
    </div>
    
    <div class="top-bar">
        <div class="page-title">Admin Management</div>
    </div>
    
    <div class="content-wrapper">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-title">Total Admin Users</div>
                    <div class="stat-value"><?php echo $total_admins; ?></div>
                </div>
                <i class="fas fa-user-shield stat-icon"></i>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-title">Active Admins</div>
                    <div class="stat-value"><?php echo $active_admins; ?></div>
                </div>
                <i class="fas fa-user-check stat-icon"></i>
            </div>
        </div>
        
        <div class="content-section">
            <div class="section-header">
                <div class="section-title">Admin Users</div>
                <?php if ($is_super_admin): ?>
                <button class="control-button" id="create-admin-btn">
                    <i class="fas fa-plus"></i> Add New Admin
                </button>
                <?php endif; ?>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>USER</th>
                        <th>EMAIL</th>
                        <th>PHONE</th>
                        <th>ROLE</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    mysqli_data_seek($admins_result, 0);
                    while ($admin = mysqli_fetch_assoc($admins_result)): 
                        $first_letter = strtoupper(substr($admin['first_name'], 0, 1));
                        $display_name = htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']);
                    ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar"><?php echo $first_letter; ?></div>
                                    <div class="user-details">
                                        <div class="user-name"><?php echo $display_name; ?></div>
                                        <div class="user-username">@<?php echo htmlspecialchars($admin['username']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><?php echo htmlspecialchars($admin['phone']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $admin['role']; ?>">
                                    <?php echo str_replace('_', ' ', ucfirst($admin['role'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $admin['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($is_super_admin || $admin['id'] == $current_admin_id): ?>
                                        <button class="action-button edit-button" data-id="<?php echo $admin['id']; ?>" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($is_super_admin && $admin['id'] != $current_admin_id): ?>
                                        <button class="action-button delete-button" data-id="<?php echo $admin['id']; ?>" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Create/Edit Admin Modal -->
    <div class="modal" id="admin-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="modal-title">Add New Admin</div>
                <button class="close-modal"><i class="fas fa-times"></i></button>
            </div>
            
            <form id="admin-form" method="POST">
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="admin_id" id="admin-id">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <span id="password-note" style="font-size: 12px; color: #999; display: none;">(leave blank to keep current)</span></label>
                    <div class="password-input-container">
                        <input type="password" id="password" name="password">
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="password-icon"></i>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" pattern="[0-9]{11}" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                
                <div class="form-group" id="status-group" style="display: none;">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" checked>
                        <label for="is_active">Account Active</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="submit-button">Save Admin</button>
                    <button type="button" class="cancel-button" id="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const adminModal = document.getElementById('admin-modal');
            const createAdminBtn = document.getElementById('create-admin-btn');
            const closeModal = document.querySelector('.close-modal');
            const cancelBtn = document.getElementById('cancel-btn');
            const adminForm = document.getElementById('admin-form');
            const formAction = document.getElementById('form-action');
            const modalTitle = document.getElementById('modal-title');
            const passwordNote = document.getElementById('password-note');
            const statusGroup = document.getElementById('status-group');
            const usernameField = document.getElementById('username');
            const isSuperAdmin = <?php echo $is_super_admin ? 'true' : 'false'; ?>;
            
            // Only super admin can create new admins
            if (createAdminBtn) {
                createAdminBtn.addEventListener('click', () => {
                    modalTitle.textContent = 'Add New Admin';
                    formAction.value = 'create';
                    adminForm.reset();
                    passwordNote.style.display = 'none';
                    document.getElementById('password').required = true;
                    statusGroup.style.display = 'none';
                    usernameField.disabled = false;
                    adminModal.style.display = 'flex';
                });
            }
            
            // Edit admin
            document.querySelectorAll('.edit-button').forEach(button => {
                button.addEventListener('click', async () => {
                    const adminId = button.getAttribute('data-id');
                    const isEditingSelf = adminId == <?php echo $current_admin_id; ?>;
                    
                    // Fetch admin data
                    const response = await fetch(`get-admin-data.php?id=${adminId}`);
                    const admin = await response.json();
                    
                    modalTitle.textContent = isEditingSelf ? 'Edit My Profile' : 'Edit Admin';
                    formAction.value = 'update';
                    document.getElementById('admin-id').value = adminId;
                    document.getElementById('username').value = admin.username;
                    document.getElementById('username').disabled = true;
                    document.getElementById('email').value = admin.email;
                    document.getElementById('first_name').value = admin.first_name;
                    document.getElementById('last_name').value = admin.last_name;
                    document.getElementById('phone').value = admin.phone;
                    document.getElementById('role').value = admin.role;
                    document.getElementById('is_active').checked = admin.is_active == 1;
                    
                    // Regular admins can't change role or status
                    if (!isSuperAdmin) {
                        document.getElementById('role').disabled = true;
                        document.getElementById('is_active').disabled = true;
                    } else if (isEditingSelf && admin.role === 'super_admin') {
                        // Super admin can't change their own role
                        document.getElementById('role').disabled = true;
                    } else {
                        document.getElementById('role').disabled = false;
                        document.getElementById('is_active').disabled = false;
                    }
                    
                    passwordNote.style.display = 'inline';
                    document.getElementById('password').required = false;
                    document.getElementById('password').value = '';
                    statusGroup.style.display = isSuperAdmin ? 'block' : 'none';
                    
                    adminModal.style.display = 'flex';
                });
            });
            
            // Delete admin
            document.querySelectorAll('.delete-button').forEach(button => {
                button.addEventListener('click', () => {
                    const adminId = button.getAttribute('data-id');
                    
                    if (confirm('Are you sure you want to delete this admin user?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="admin_id" value="${adminId}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
            
            // Close modal
            closeModal.addEventListener('click', () => {
                adminModal.style.display = 'none';
                resetForm();
            });
            
            cancelBtn.addEventListener('click', () => {
                adminModal.style.display = 'none';
                resetForm();
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', (event) => {
                if (event.target === adminModal) {
                    adminModal.style.display = 'none';
                    resetForm();
                }
            });
            
            function resetForm() {
                document.getElementById('role').disabled = false;
                document.getElementById('is_active').disabled = false;
                document.getElementById('password').type = 'password';
                document.getElementById('password-icon').classList.remove('fa-eye-slash');
                document.getElementById('password-icon').classList.add('fa-eye');
            }
            
            // Form validation
            adminForm.addEventListener('submit', (e) => {
                const password = document.getElementById('password').value;
                const action = formAction.value;
                
                if (action === 'create' && password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long!');
                    return;
                }
                
                if (action === 'update' && password.length > 0 && password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long!');
                    return;
                }
            });
        });
    </script>
</body>
</html>