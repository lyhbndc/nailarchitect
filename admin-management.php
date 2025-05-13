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
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
            $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
            $phone = mysqli_real_escape_string($conn, $_POST['phone']);
            $role = mysqli_real_escape_string($conn, $_POST['role']);
            
            // Check if trying to create super_admin and one already exists
            if ($role === 'super_admin') {
                $check_super = "SELECT COUNT(*) as count FROM admin_users WHERE role = 'super_admin'";
                $super_result = mysqli_query($conn, $check_super);
                $super_count = mysqli_fetch_assoc($super_result)['count'];
                
                if ($super_count > 0) {
                    $error_message = "Only one super admin is allowed in the system!";
                } else {
                    $query = "INSERT INTO admin_users (username, email, password, first_name, last_name, phone, role) 
                              VALUES ('$username', '$email', '$password', '$first_name', '$last_name', '$phone', '$role')";
                    
                    if (mysqli_query($conn, $query)) {
                        $success_message = "Admin user created successfully!";
                    } else {
                        $error_message = "Error creating admin user: " . mysqli_error($conn);
                    }
                }
            } else {
                $query = "INSERT INTO admin_users (username, email, password, first_name, last_name, phone, role) 
                          VALUES ('$username', '$email', '$password', '$first_name', '$last_name', '$phone', '$role')";
                
                if (mysqli_query($conn, $query)) {
                    $success_message = "Admin user created successfully!";
                } else {
                    $error_message = "Error creating admin user: " . mysqli_error($conn);
                }
            }
        }
        
        // Only super admin can update other admins
        if ($action == 'update' && $is_super_admin) {
            $admin_id = $_POST['admin_id'];
            $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
            $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $phone = mysqli_real_escape_string($conn, $_POST['phone']);
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
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $query = "UPDATE admin_users SET 
                                  first_name = '$first_name',
                                  last_name = '$last_name',
                                  email = '$email',
                                  phone = '$phone',
                                  role = '$role',
                                  is_active = $is_active,
                                  password = '$password'
                                  WHERE id = $admin_id";
                    }
                    
                    if (mysqli_query($conn, $query)) {
                        $success_message = "Admin user updated successfully!";
                    } else {
                        $error_message = "Error updating admin user: " . mysqli_error($conn);
                    }
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
        
        // Show error if non-super admin tries to perform actions
        if (!$is_super_admin && in_array($action, ['create', 'update', 'delete'])) {
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
        }
        
        .control-button:hover {
            background: linear-gradient(to right, #d98d8d, #ce7878);
        }
        
        .control-button.disabled {
            background-color: #e0e0e0;
            color: #999;
            cursor: not-allowed;
        }
        
        .control-button.disabled:hover {
            background-color: #e0e0e0;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid rgb(168, 142, 142);
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        
        .admin-table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgb(196, 174, 174);
            font-size: 14px;
        }
        
        .admin-table tr:hover {
            background: linear-gradient(to right, rgb(233, 171, 171), rgb(226, 178, 178));
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-active {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .status-inactive {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            background-color: #e0e0e0;
            color: #616161;
        }
        
        .role-super_admin {
            background-color: #fff9c4;
            color: #f57f17;
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
        
        .action-button.disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }
        
        .action-button.disabled:hover {
            transform: none;
            background-color: rgba(255,255,255,0.5);
        }
        
        .edit-button {
            color: #1565c0;
        }
        
        .delete-button {
            color: #c62828;
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
            max-width: 600px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .submit-button {
            padding: 10px 20px;
            background-color: #ae9389;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .submit-button:hover {
            background-color: #8b6f5f;
        }
        
        .cancel-button {
            padding: 10px 20px;
            background-color: #d9bbb0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .cancel-button:hover {
            background-color: #c0a297;
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .alert-error {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .no-permission {
            text-align: center;
            padding: 40px;
            background-color: #f8f8f8;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .no-permission i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }
        
        .no-permission p {
            font-size: 16px;
            color: #666;
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
            
            <div class="menu-item" onclick="window.location.href='logout.php'">
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
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Admin Users</div>
                        <div class="stat-value"><?php echo $total_admins; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Active Admins</div>
                        <div class="stat-value"><?php echo $active_admins; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-section">
            <div class="section-header">
                <div class="section-title">Admin Users</div>
                <?php if ($is_super_admin): ?>
                <div class="section-controls">
                    <div class="control-button" id="create-admin-btn">
                        <i class="fas fa-plus"></i> Add New Admin
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!$is_super_admin): ?>
            <div class="no-permission">
                <i class="fas fa-lock"></i>
                <p>Only Super Admins can manage admin users.</p>
            </div>
            <?php endif; ?>
            
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
                        
                        // Truncate display name if too long
                        if (strlen($display_name) > 20) {
                            $display_name = substr($display_name, 0, 18) . '...';
                        }
                    ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar"><?php echo $first_letter; ?></div>
                                    <div>
                                        <div><?php echo $display_name; ?></div>
                                        <div style="font-size: 12px; color: #666;">@<?php echo htmlspecialchars($admin['username']); ?></div>
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
                            <td class="action-cell">
                                <?php if ($is_super_admin): ?>
                                    <div class="action-button edit-button" data-id="<?php echo $admin['id']; ?>" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <?php if ($admin['id'] != $current_admin_id): ?>
                                        <div class="action-button delete-button" data-id="<?php echo $admin['id']; ?>" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="action-button disabled" title="Cannot delete yourself">
                                            <i class="fas fa-trash"></i>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="action-button disabled" title="No permissions">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                <?php endif; ?>
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
                <div class="close-modal"><i class="fas fa-times"></i></div>
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
                    <label for="password">Password <span id="password-note" style="font-size: 12px; color: #666;">(leave blank to keep current)</span></label>
                    <input type="password" id="password" name="password">
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
                        <option value="super_admin" id="super_admin_option">Super Admin</option>
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
                    if (!isSuperAdmin) {
                        alert('Only super admins can create new admin users!');
                        return;
                    }
                    
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
            
            // Edit admin - only for super admins
            document.querySelectorAll('.edit-button').forEach(button => {
                button.addEventListener('click', async () => {
                    if (!isSuperAdmin) {
                        alert('Only super admins can edit admin users!');
                        return;
                    }
                    
                    const adminId = button.getAttribute('data-id');
                    const isEditingSelf = adminId == <?php echo $current_admin_id; ?>;
                    
                    // Fetch admin data
                    const response = await fetch(`get-admin-data.php?id=${adminId}`);
                    const admin = await response.json();
                    
                    modalTitle.textContent = 'Edit Admin';
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
                    
                    // If editing self and is super admin, disable role change
                    if (isEditingSelf && admin.role === 'super_admin') {
                        document.getElementById('role').disabled = true;
                        document.getElementById('super_admin_option').disabled = true;
                    } else {
                        document.getElementById('role').disabled = false;
                        document.getElementById('super_admin_option').disabled = false;
                    }
                    
                    passwordNote.style.display = 'inline';
                    document.getElementById('password').required = false;
                    statusGroup.style.display = 'block';
                    
                    adminModal.style.display = 'flex';
                });
            });
            
            // Delete admin - only for super admins
            document.querySelectorAll('.delete-button').forEach(button => {
                button.addEventListener('click', () => {
                    if (!isSuperAdmin) {
                        alert('Only super admins can delete admin users!');
                        return;
                    }
                    
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
                document.getElementById('role').disabled = false;
            });
            
            cancelBtn.addEventListener('click', () => {
                adminModal.style.display = 'none';
                document.getElementById('role').disabled = false;
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', (event) => {
                if (event.target === adminModal) {
                    adminModal.style.display = 'none';
                    document.getElementById('role').disabled = false;
                }
            });
            
            // Check if super admin exists before allowing creation of another
            if (document.getElementById('role')) {
                document.getElementById('role').addEventListener('change', async (e) => {
                    if (e.target.value === 'super_admin' && formAction.value === 'create') {
                        const response = await fetch('check-super-admin.php');
                        const result = await response.json();
                        
                        if (result.exists) {
                            alert('Only one super admin is allowed in the system!');
                            e.target.value = 'admin';
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>