<?php
// simple-backup.php - Simplified backup system with admin design
session_start();

date_default_timezone_set('Asia/Manila');

// Basic configuration
$config = [
    'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
    'db_user' => $_ENV['DB_USER'] ?? 'root',
    'db_pass' => $_ENV['DB_PASS'] ?? '',
    'db_name' => $_ENV['DB_NAME'] ?? 'nail_architect_db',
    'backup_dir' => __DIR__ . '/backups/',
    'max_backups' => 10 // Keep only latest 10 backups
];

// Create backup directory if it doesn't exist
if (!file_exists($config['backup_dir'])) {
    mkdir($config['backup_dir'], 0755, true);
}

// Initialize message
$message = '';

// Handle database connection
function getConnection($config) {
    $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Create backup
if (isset($_POST['create_backup'])) {
    try {
        $conn = getConnection($config);
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $config['backup_dir'] . $filename;
        
        // Start output buffering
        ob_start();
        
        // Header
        echo "-- Database Backup\n";
        echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        echo "-- Database: " . $config['db_name'] . "\n\n";
        echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        echo "SET time_zone = \"+00:00\";\n\n";
        
        // Get all tables
        $tables = array();
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        // Export each table
        foreach ($tables as $table) {
            echo "\n-- Table structure for table `$table`\n";
            echo "DROP TABLE IF EXISTS `$table`;\n";
            
            $result = $conn->query("SHOW CREATE TABLE `$table`");
            $row = $result->fetch_row();
            echo $row[1] . ";\n\n";
            
            // Get data
            $result = $conn->query("SELECT * FROM `$table`");
            if ($result->num_rows > 0) {
                echo "-- Data for table `$table`\n";
                while ($row = $result->fetch_assoc()) {
                    $values = array_map(function($value) use ($conn) {
                        return $value === null ? 'NULL' : "'" . $conn->real_escape_string($value) . "'";
                    }, $row);
                    echo "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                }
            }
            echo "\n";
        }
        
        // Save to file
        $backup_content = ob_get_clean();
        file_put_contents($filepath, $backup_content);
        
        $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Backup created successfully: $filename</div>";
        
        // Clean up old backups
        cleanOldBackups($config['backup_dir'], $config['max_backups']);
        
        $conn->close();
    } catch (Exception $e) {
        $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Error creating backup: " . $e->getMessage() . "</div>";
    }
}

// Restore backup
if (isset($_POST['restore_backup']) && !empty($_POST['backup_file'])) {
    try {
        $conn = getConnection($config);
        $filename = basename($_POST['backup_file']);
        $filepath = $config['backup_dir'] . $filename;
        
        if (!file_exists($filepath)) {
            throw new Exception("Backup file not found");
        }
        
        // Read backup file
        $sql = file_get_contents($filepath);
        
        // Disable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Execute queries
        $conn->multi_query($sql);
        
        // Wait for all queries to complete
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Database restored successfully from: $filename</div>";
        
        $conn->close();
    } catch (Exception $e) {
        $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Error restoring backup: " . $e->getMessage() . "</div>";
    }
}

// Delete backup
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $filename = basename($_GET['delete']);
    $filepath = $config['backup_dir'] . $filename;
    
    if (file_exists($filepath) && unlink($filepath)) {
        $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Backup deleted successfully</div>";
    } else {
        $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Error deleting backup</div>";
    }
}

// Download backup
if (isset($_GET['download']) && !empty($_GET['download'])) {
    $filename = basename($_GET['download']);
    $filepath = $config['backup_dir'] . $filename;
    
    if (file_exists($filepath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}

// Clean old backups function
function cleanOldBackups($dir, $keep) {
    $files = glob($dir . '*.sql');
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    if (count($files) > $keep) {
        $toDelete = array_slice($files, $keep);
        foreach ($toDelete as $file) {
            unlink($file);
        }
    }
}

// Get backup files
function getBackupFiles($dir) {
    $files = glob($dir . '*.sql');
    $backups = [];
    
    foreach ($files as $file) {
        $backups[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => filemtime($file)
        ];
    }
    
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
    
    return $backups;
}

$backups = getBackupFiles($config['backup_dir']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup & Restore - Nail Architect Admin</title>
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
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            color: #333;
        }
        
        .btn-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn-success {
            background-color: #d4edda;
            color: #155724;
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
            background-color: white;
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
        
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
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
            
            <div class="menu-item" onclick="window.location.href='admin-inquiries.php'">
                <div class="menu-icon"><i class="fas fa-question-circle"></i></div>
                <div class="menu-text">Inquiries</div>
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
        <?php if ($message): ?>
            <?php echo $message; ?>
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
                    <form method="post" style="display: inline;">
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
                    <?php if (count($backups) > 0): ?>
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
                
                <form method="post" onsubmit="return confirm('Are you sure? This will replace all current data.');">
                    <div class="form-group">
                        <label for="backup_file">Select Backup to Restore:</label>
                        <select name="backup_file" id="backup_file" required>
                            <option value="">-- Select Backup File --</option>
                            <?php foreach ($backups as $backup): ?>
                                <option value="<?php echo htmlspecialchars($backup['name']); ?>">
                                    <?php echo htmlspecialchars($backup['name']); ?> 
                                    (<?php echo date('F j, Y g:i a', $backup['date']); ?>) - 
                                    <?php echo number_format($backup['size'] / 1024, 2); ?> KB
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
            
            <?php if (count($backups) > 0): ?>
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
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-file-code" style="color: #666;"></i>
                                        <?php echo htmlspecialchars($backup['name']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="file-date">
                                        <i class="far fa-calendar-alt" style="margin-right: 5px;"></i>
                                        <?php echo date('F j, Y g:i a', $backup['date']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="file-size">
                                        <?php echo number_format($backup['size'] / 1024, 2); ?> KB
                                    </div>
                                </td>
                                <td class="backup-actions">
                                    <a href="?download=<?php echo urlencode($backup['name']); ?>" 
                                       class="btn btn-success">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <a href="?delete=<?php echo urlencode($backup['name']); ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Delete this backup?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-database"></i>
                    <p>No backups found. Click "Create Database Backup" to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function showRestoreForm() {
            document.getElementById('restore-form').style.display = 'block';
            document.getElementById('restore-form').scrollIntoView({ behavior: 'smooth' });
        }

        function hideRestoreForm() {
            document.getElementById('restore-form').style.display = 'none';
        }
    </script>
</body>
</html>