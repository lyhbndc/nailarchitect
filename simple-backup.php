<?php
// simple-backup.php - Simplified backup system for local and AWS deployment
session_start();

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
        
        $message = "<div class='alert success'>Backup created successfully: $filename</div>";
        
        // Clean up old backups
        cleanOldBackups($config['backup_dir'], $config['max_backups']);
        
        $conn->close();
    } catch (Exception $e) {
        $message = "<div class='alert error'>Error creating backup: " . $e->getMessage() . "</div>";
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
        
        $message = "<div class='alert success'>Database restored successfully from: $filename</div>";
        
        $conn->close();
    } catch (Exception $e) {
        $message = "<div class='alert error'>Error restoring backup: " . $e->getMessage() . "</div>";
    }
}

// Delete backup
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $filename = basename($_GET['delete']);
    $filepath = $config['backup_dir'] . $filename;
    
    if (file_exists($filepath) && unlink($filepath)) {
        $message = "<div class='alert success'>Backup deleted successfully</div>";
    } else {
        $message = "<div class='alert error'>Error deleting backup</div>";
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
    <title>Database Backup Manager</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        h1 {
            margin-bottom: 30px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .file-actions {
            display: flex;
            gap: 10px;
        }
        
        .file-size {
            color: #666;
            font-size: 14px;
        }
        
        .file-date {
            color: #666;
            font-size: 14px;
        }
        
        .restore-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #ffeaa7;
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
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            table {
                font-size: 14px;
            }
            
            .file-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-database"></i> Database Backup Manager</h1>
        
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        
        <div class="actions">
            <form method="post" style="display: inline;">
                <button type="submit" name="create_backup" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Backup
                </button>
            </form>
            
            <?php if (count($backups) > 0): ?>
                <button onclick="toggleRestore()" class="btn btn-danger">
                    <i class="fas fa-undo"></i> Restore Database
                </button>
            <?php endif; ?>
        </div>
        
        <div id="restore-form" class="restore-form" style="display: none;">
            <div class="warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Warning:</strong> Restoring will replace all current data in the database.
            </div>
            
            <form method="post" onsubmit="return confirm('Are you sure? This will replace all current data.');">
                <select name="backup_file" required>
                    <option value="">Select a backup to restore</option>
                    <?php foreach ($backups as $backup): ?>
                        <option value="<?php echo htmlspecialchars($backup['name']); ?>">
                            <?php echo htmlspecialchars($backup['name']); ?> 
                            (<?php echo date('Y-m-d H:i:s', $backup['date']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <div style="margin-top: 15px;">
                    <button type="submit" name="restore_backup" class="btn btn-danger">
                        <i class="fas fa-undo"></i> Restore Database
                    </button>
                    <button type="button" onclick="toggleRestore()" class="btn btn-primary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
        
        <?php if (count($backups) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Backup File</th>
                        <th>Date Created</th>
                        <th>Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td>
                                <i class="fas fa-file-code"></i>
                                <?php echo htmlspecialchars($backup['name']); ?>
                            </td>
                            <td class="file-date">
                                <?php echo date('Y-m-d H:i:s', $backup['date']); ?>
                            </td>
                            <td class="file-size">
                                <?php echo number_format($backup['size'] / 1024, 2); ?> KB
                            </td>
                            <td>
                                <div class="file-actions">
                                    <a href="?download=<?php echo urlencode($backup['name']); ?>" 
                                       class="btn btn-success">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <a href="?delete=<?php echo urlencode($backup['name']); ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Delete this backup?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-database"></i>
                <p>No backups found. Click "Create Backup" to get started.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function toggleRestore() {
            const form = document.getElementById('restore-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>