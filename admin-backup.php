<?php
// Start session and check admin authentication
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_SESSION['backup_message'])) {
    $backup_message = $_SESSION['backup_message'];
    unset($_SESSION['backup_message']);
}

// Define backup directory - make sure this exists and is writable
$backup_dir = "backups/";
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Handle Backup Process
$backup_message = '';
if (isset($_POST['create_backup'])) {
    $timestamp = date("Y-m-d-H-i-s");
    $backup_file = $backup_dir . "nail_architect_backup_" . $timestamp . ".sql";

    // Get all tables
    $tables = [];
    $result = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }

    $output = "-- Nail Architect Database Backup\n";
    $output .= "-- Generated: " . date("Y-m-d H:i:s") . "\n\n";

    // Export each table
    foreach ($tables as $table) {
        // Table creation
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        $result = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
        $row = mysqli_fetch_row($result);
        $output .= $row[1] . ";\n\n";

        // Table data
        $result = mysqli_query($conn, "SELECT * FROM `$table`");
        $num_fields = mysqli_num_fields($result);
        
        while ($row = mysqli_fetch_row($result)) {
            $output .= "INSERT INTO `$table` VALUES(";
            for ($i = 0; $i < $num_fields; $i++) {
                if (isset($row[$i])) {
                    $row[$i] = addslashes($row[$i]);
                    $row[$i] = str_replace("\n", "\\n", $row[$i]);
                    $output .= '"' . $row[$i] . '"';
                } else {
                    $output .= 'NULL';
                }
                if ($i < ($num_fields - 1)) {
                    $output .= ',';
                }
            }
            $output .= ");\n";
        }
        $output .= "\n\n";
    }

    // Save to file
    if (file_put_contents($backup_file, $output)) {
        $backup_message = "<div class='alert success'>Backup created successfully: " . basename($backup_file) . "</div>";
    } else {
        $backup_message = "<div class='alert error'>Error creating backup file. Please check directory permissions.</div>";
    }
}

// Handle Backup File Deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $filename = basename($_GET['delete']);
    $file_path = $backup_dir . $filename;
    
    // Security check to ensure we're only deleting .sql files from the backup directory
    if (strpos($filename, '..') === false && file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'sql') {
        if (unlink($file_path)) {
            $backup_message = "<div class='alert success'>Backup file deleted successfully.</div>";
        } else {
            $backup_message = "<div class='alert error'>Error deleting backup file.</div>";
        }
    } else {
        $backup_message = "<div class='alert error'>Invalid backup file specified.</div>";
    }
}

// Handle Restore Process
if (isset($_POST['restore_backup']) && !empty($_POST['backup_file'])) {
    $filename = basename($_POST['backup_file']);
    $file_path = $backup_dir . $filename;
    
    // Security check
    if (strpos($filename, '..') === false && file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'sql') {
        // Read backup file
        $sql = file_get_contents($file_path);
        
        // Split into individual queries
        $queries = explode(';', $sql);
        
        // Execute each query
        $error = false;
        mysqli_autocommit($conn, false); // Start transaction
        
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $result = mysqli_query($conn, $query . ';');
                if (!$result) {
                    $error = true;
                    break;
                }
            }
        }
        
        if ($error) {
            mysqli_rollback($conn);
            $backup_message = "<div class='alert error'>Error restoring database. MySQL Error: " . mysqli_error($conn) . "</div>";
        } else {
            mysqli_commit($conn);
            $backup_message = "<div class='alert success'>Database restored successfully from " . $filename . "</div>";
        }
    } else {
        $backup_message = "<div class='alert error'>Invalid backup file specified.</div>";
    }
}

// Get list of backup files
$backup_files = [];
if ($handle = opendir($backup_dir)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && pathinfo($file, PATHINFO_EXTENSION) == "sql") {
            $backup_files[] = [
                'name' => $file,
                'size' => filesize($backup_dir . $file),
                'date' => date("Y-m-d H:i:s", filemtime($backup_dir . $file))
            ];
        }
    }
    closedir($handle);
}

// Sort backup files by date (newest first)
usort($backup_files, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup & Restore - Nail Architect Admin</title>
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
        
        .content-section {
            background-color: #E8D7D0;
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
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .backup-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .backup-table th, .backup-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        
        .backup-table th {
            background-color: rgba(0,0,0,0.05);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            color: #555;
        }
        
        .backup-table tr:last-child td {
            border-bottom: none;
        }
        
        .backup-table tr:hover td {
            background-color: rgba(0,0,0,0.02);
        }
        
        .backup-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .btn-primary {
            background-color: #D9BBB0;
            color: #333;
        }
        
        .btn-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .warning-note {
            background-color: #fff3cd;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            color: #856404;
            border-left: 4px solid #ffc107;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .warning-note i {
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: white;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .form-group select:focus {
            outline: none;
            border-color: #D9BBB0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background-color: rgba(255,255,255,0.3);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .card-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background-color: rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .card-title {
            font-weight: 600;
            font-size: 16px;
        }
        
        .card-body {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }
        
        .card-footer {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }
        
        .file-size {
            font-size: 13px;
            color: #666;
            background-color: rgba(0,0,0,0.05);
            padding: 3px 10px;
            border-radius: 20px;
        }
        
        .file-date {
            font-size: 13px;
            color: #666;
        }
        
        /* Responsive Media Queries */
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
            .content-wrapper {
                padding: 15px;
                padding-top: 70px;
            }
            
            .backup-table {
                display: block;
                overflow-x: auto;
            }
            
            .card-grid {
                grid-template-columns: 1fr;
            }
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
    
    <div class="menu-item" onclick="window.location.href='clients.php'">
        <div class="menu-icon"><i class="fas fa-users"></i></div>
        <div class="menu-text">Clients</div>
    </div>
    
    <div class="menu-item" onclick="window.location.href='admin-messages.php'">
        <div class="menu-icon"><i class="fas fa-envelope"></i></div>
        <div class="menu-text">Messages</div>
    </div>
    
    <div class="menu-section">SYSTEM</div>
    
    <div class="menu-item active" onclick="window.location.href='admin-backup.php'">
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
        <div class="page-title">Database Backup & Restore</div>
    </div>
    
    <div class="content-wrapper">
        <?php if (!empty($backup_message)): ?>
            <?php echo $backup_message; ?>
        <?php endif; ?>
        
        <div class="card-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="card-title">Create Database Backup</div>
                </div>
                <div class="card-body">
                    Creating a backup will save the current state of your database. It's recommended to create backups regularly, especially before major changes to your website.
                </div>
                <div class="card-footer">
                    <form method="post" action="">
                        <button type="submit" name="create_backup" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Database Backup
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #fff3cd; color: #856404;">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div class="card-title">Restore Database</div>
                </div>
                <div class="card-body">
                    Restoring from a backup will replace your current database with the backup version. This action cannot be undone. Make sure to create a new backup before restoring.
                </div>
                <div class="card-footer">
                    <?php if (count($backup_files) > 0): ?>
                        <button type="button" onclick="showRestoreForm()" class="btn btn-danger">
                            <i class="fas fa-undo"></i> Restore Database
                        </button>
                    <?php else: ?>
                        <button type="button" disabled class="btn btn-danger" style="opacity: 0.6; cursor: not-allowed;">
                            <i class="fas fa-undo"></i> No Backups Available
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #d1ecf1; color: #0c5460;">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="card-title">Backup Media Files</div>
                </div>
                <div class="card-body">
                    This will create a zip archive of all uploaded images and media files from your website. Media backups are stored separately from database backups.
                </div>
                <div class="card-footer">
                    <form method="post" action="create-media-backup.php">
                        <button type="submit" name="media_backup" class="btn btn-info">
                            <i class="fas fa-file-archive"></i> Backup Media Files
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Restore Form (Hidden by Default) -->
        <div id="restore-form" style="display: none;">
            <div class="content-section">
                <div class="section-header">
                    <div class="section-title">Restore Database</div>
                </div>
                
                <div class="warning-note">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Warning:</strong> Restoring from a backup will replace your current database with the backup version. This action cannot be undone.
                    </div>
                </div>
                
                <form method="post" action="" onsubmit="return confirm('Are you sure you want to restore the database from this backup? All current data will be replaced.');">
                    <div class="form-group">
                        <label for="backup_file">Select Backup to Restore:</label>
                        <select name="backup_file" id="backup_file" required>
                            <option value="">-- Select Backup File --</option>
                            <?php foreach ($backup_files as $file): ?>
                                <option value="<?php echo htmlspecialchars($file['name']); ?>">
                                    <?php echo htmlspecialchars($file['name']); ?> 
                                    (<?php echo date('F j, Y g:i a', strtotime($file['date'])); ?>) - 
                                    <?php echo round($file['size'] / 1024, 2); ?> KB
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="restore_backup" class="btn btn-danger">
                            <i class="fas fa-undo"></i> Restore Database
                        </button>
                        <button type="button" onclick="hideRestoreForm()" class="btn btn-primary">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="content-section">
            <div class="section-header">
                <div class="section-title">Manage Backup Files</div>
            </div>
            
            <?php if (count($backup_files) > 0): ?>
                <table class="backup-table">
                    <thead>
                        <tr>
                            <th>Backup File</th>
                            <th>Created Date</th>
                            <th>Size</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backup_files as $file): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-file-code" style="color: #666;"></i>
                                        <?php echo htmlspecialchars($file['name']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="file-date">
                                        <i class="far fa-calendar-alt" style="margin-right: 5px;"></i>
                                        <?php echo date('F j, Y g:i a', strtotime($file['date'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="file-size">
                                        <?php echo round($file['size'] / 1024, 2); ?> KB
                                    </div>
                                </td>
                                <td class="backup-actions">
                                    <a href="<?php echo $backup_dir . $file['name']; ?>" class="btn btn-primary" download>
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <a href="?delete=<?php echo urlencode($file['name']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this backup file?')">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert info">
                    <i class="fas fa-info-circle"></i>
                    <div>No backup files found. Create a backup first.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function showRestoreForm() {
            document.getElementById('restore-form').style.display = 'block';
            // Smoothly scroll to the form
            document.getElementById('restore-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function hideRestoreForm() {
            document.getElementById('restore-form').style.display = 'none';
        }
        
        // Add loading indicator for buttons
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const button = this.querySelector('button[type="submit"]');
                    if (button) {
                        const originalText = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                        button.disabled = true;
                        
                        // Store the original text to restore later if needed
                        button.setAttribute('data-original-text', originalText);
                        
                        // If form submission takes too long, restore the button after a timeout
                        setTimeout(() => {
                            if (button.disabled) {
                                button.innerHTML = originalText;
                                button.disabled = false;
                            }
                        }, 10000); // 10 seconds timeout
                    }
                });
            });
        });
    </script>
</body>
</html>