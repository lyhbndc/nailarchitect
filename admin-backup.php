<?php
// backup-system.php - Enhanced backup system with automatic save points
session_start();

date_default_timezone_set('Asia/Manila');

// Expanded configuration
$config = [
    // Database settings
    'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
    'db_user' => $_ENV['DB_USER'] ?? 'root',
    'db_pass' => $_ENV['DB_PASS'] ?? '',
    'db_name' => $_ENV['DB_NAME'] ?? 'nail_architect_db',
    
    // Backup directories
    'backup_dir' => __DIR__ . '/backups/',
    'db_backup_dir' => __DIR__ . '/backups/database/',
    'files_backup_dir' => __DIR__ . '/backups/files/',
    
    // Retention settings
    'max_backups' => 10, // Keep only latest 10 backups
    
    // Website root directory to backup (excluding backup directory)
    'website_root' => __DIR__,
    
    // Directories/files to exclude from website backup
    'exclude_paths' => [
        '/backups/',
        '/node_modules/',
        '/.git/',
        '/.env',
    ],
    
    // Automatic backup settings
    'auto_backup_enabled' => true,
    'auto_backup_frequency' => 'daily', // Options: hourly, daily, weekly
    'auto_backup_time' => '03:00', // For daily/weekly backups (24-hour format)
    'auto_backup_day' => 'Sunday', // For weekly backups
    'auto_backup_keep' => 7, // Number of automatic backups to keep
];

// Create all necessary backup directories
foreach (['backup_dir', 'db_backup_dir', 'files_backup_dir'] as $dir) {
    if (!file_exists($config[$dir])) {
        mkdir($config[$dir], 0755, true);
    }
}

// Include auto-backup handler
define('ADMIN_ACCESS', true);
require_once('auto-backup-handler.php');

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

// Save backup schedule settings
if (isset($_POST['save_schedule'])) {
    $config['auto_backup_enabled'] = isset($_POST['auto_backup_enabled']);
    $config['auto_backup_frequency'] = $_POST['auto_backup_frequency'];
    $config['auto_backup_time'] = $_POST['auto_backup_time'];
    $config['auto_backup_day'] = $_POST['auto_backup_day'];
    $config['auto_backup_keep'] = (int)$_POST['auto_backup_keep'];
    
    // Save settings to a configuration file
    $configFile = __DIR__ . '/backup-config.php';
    file_put_contents($configFile, "<?php\nreturn " . var_export($config, true) . ";\n?>");
    
    $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Backup schedule settings saved successfully</div>";
}

// Create database backup function
function createDatabaseBackup($config, $isAutomatic = false) {
    try {
        $conn = getConnection($config);
        $prefix = $isAutomatic ? 'auto_' : '';
        $filename = $prefix . 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $config['db_backup_dir'] . $filename;
        
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
        
        // Clean up old backups based on type
        if ($isAutomatic) {
            cleanOldBackups($config['db_backup_dir'], $config['auto_backup_keep'], 'auto_db_');
        } else {
            cleanOldBackups($config['db_backup_dir'], $config['max_backups'], 'db_');
        }
        
        $conn->close();
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $filepath,
            'size' => filesize($filepath)
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Create file/webpage backup function
function createFilesBackup($config, $isAutomatic = false) {
    try {
        $prefix = $isAutomatic ? 'auto_' : '';
        $filename = $prefix . 'files_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $filepath = $config['files_backup_dir'] . $filename;
        
        // Create new ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot create ZIP archive");
        }
        
        // Initialize file count
        $fileCount = 0;
        
        // Get real path for website root
        $rootPath = realpath($config['website_root']);
        
        // Create recursive directory iterator
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        // Add files to ZIP
        foreach ($files as $file) {
            // Skip directories (they get added automatically)
            if ($file->isDir()) {
                continue;
            }
            
            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);
            
            // Check if file should be excluded
            $exclude = false;
            foreach ($config['exclude_paths'] as $excludePath) {
                if (strpos($relativePath, ltrim($excludePath, '/')) === 0) {
                    $exclude = true;
                    break;
                }
            }
            
            // Skip if file should be excluded
            if ($exclude) {
                continue;
            }
            
            // Add current file to archive
            $zip->addFile($filePath, $relativePath);
            $fileCount++;
        }
        
        // Close ZIP file
        $zip->close();
        
        // Clean up old backups based on type
        if ($isAutomatic) {
            cleanOldBackups($config['files_backup_dir'], $config['auto_backup_keep'], 'auto_files_');
        } else {
            cleanOldBackups($config['files_backup_dir'], $config['max_backups'], 'files_');
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $filepath,
            'size' => filesize($filepath),
            'file_count' => $fileCount
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Create complete backup function (both database and files)
function createCompleteBackup($config, $isAutomatic = false) {
    $dbResult = createDatabaseBackup($config, $isAutomatic);
    $filesResult = createFilesBackup($config, $isAutomatic);
    
    return [
        'success' => $dbResult['success'] && $filesResult['success'],
        'db' => $dbResult,
        'files' => $filesResult
    ];
}

// Run automatic backup check
function checkAutomaticBackup($config) {
    // If automatic backups not enabled, skip
    if (!$config['auto_backup_enabled']) {
        return false;
    }
    
    // Get last automatic backup time
    $lastRunFile = $config['backup_dir'] . 'last_auto_backup.txt';
    $lastRun = file_exists($lastRunFile) ? file_get_contents($lastRunFile) : 0;
    $currentTime = time();
    
    // Check if backup should run based on frequency
    $shouldRun = false;
    
    switch ($config['auto_backup_frequency']) {
        case 'hourly':
            // Run if last backup was more than 1 hour ago
            $shouldRun = ($currentTime - $lastRun) >= 3600;
            break;
            
        case 'daily':
            // Parse scheduled time
            list($hour, $minute) = explode(':', $config['auto_backup_time']);
            $scheduledTime = strtotime(date('Y-m-d') . ' ' . $hour . ':' . $minute . ':00');
            
            // If scheduled time has passed today and no backup yet today
            $lastRunDate = date('Y-m-d', $lastRun);
            $today = date('Y-m-d');
            
            $shouldRun = ($today > $lastRunDate) && ($currentTime >= $scheduledTime);
            break;
            
        case 'weekly':
            // Check if today is the scheduled day
            $today = date('l'); // Day of the week
            $scheduledDay = $config['auto_backup_day'];
            
            if ($today == $scheduledDay) {
                // Parse scheduled time
                list($hour, $minute) = explode(':', $config['auto_backup_time']);
                $scheduledTime = strtotime(date('Y-m-d') . ' ' . $hour . ':' . $minute . ':00');
                
                // Get last run weekday
                $lastRunDay = date('l', $lastRun);
                
                // Run if it's scheduled day, time has passed, and not already run this week
                $shouldRun = ($lastRunDay != $scheduledDay) && ($currentTime >= $scheduledTime);
            }
            break;
    }
    
    // If backup should run, do it
    if ($shouldRun) {
        $result = createCompleteBackup($config, true);
        
        // Update last run time
        file_put_contents($lastRunFile, $currentTime);
        
        return $result;
    }
    
    return false;
}

// Clean old backups function
function cleanOldBackups($dir, $keep, $prefix = '') {
    $files = glob($dir . $prefix . '*.*');
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

// Restore database backup
function restoreDatabaseBackup($config, $filename) {
    try {
        $conn = getConnection($config);
        $filepath = $config['db_backup_dir'] . basename($filename);
        
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
        
        $conn->close();
        return [
            'success' => true,
            'filename' => $filename
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Restore files backup function
function restoreFilesBackup($config, $filename) {
    try {
        $filepath = $config['files_backup_dir'] . basename($filename);
        
        if (!file_exists($filepath)) {
            throw new Exception("Backup file not found");
        }
        
        $rootPath = realpath($config['website_root']);
        
        // Open ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($filepath) !== true) {
            throw new Exception("Cannot open ZIP archive");
        }
        
        // Extract all files
        $zip->extractTo($rootPath);
        $fileCount = $zip->numFiles;
        $zip->close();
        
        return [
            'success' => true,
            'filename' => $filename,
            'file_count' => $fileCount
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Get backup files function
function getBackupFiles($dir, $type = null) {
    // Define patterns based on type
    $pattern = '*.*';
    if ($type === 'database') {
        $pattern = '*.sql';
    } elseif ($type === 'files') {
        $pattern = '*.zip';
    } elseif ($type === 'auto') {
        $pattern = 'auto_*.*';
    } elseif ($type === 'manual') {
        $pattern = 'db_*.sql,files_*.zip';
    }
    
    // Split pattern by comma if multiple
    $patterns = explode(',', $pattern);
    $files = [];
    
    // Get files for each pattern
    foreach ($patterns as $pat) {
        $files = array_merge($files, glob($dir . $pat));
    }
    
    $backups = [];
    
    foreach ($files as $file) {
        $isAuto = strpos(basename($file), 'auto_') === 0;
        $isDB = strpos($file, '.sql') !== false;
        
        $backups[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => filemtime($file),
            'type' => $isDB ? 'database' : 'files',
            'auto' => $isAuto
        ];
    }
    
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
    
    return $backups;
}

// Create backup
if (isset($_POST['create_backup'])) {
    $backupType = $_POST['backup_type'] ?? 'complete';
    
    if ($backupType === 'database') {
        $result = createDatabaseBackup($config);
        if ($result['success']) {
            $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Database backup created successfully: {$result['filename']}</div>";
        } else {
            $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Error creating database backup: {$result['error']}</div>";
        }
    } elseif ($backupType === 'files') {
        $result = createFilesBackup($config);
        if ($result['success']) {
            $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Files backup created successfully: {$result['filename']} ({$result['file_count']} files)</div>";
        } else {
            $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Error creating files backup: {$result['error']}</div>";
        }
    } else {
        $result = createCompleteBackup($config);
        if ($result['success']) {
            $message = "<div class='alert success'>
                <i class='fas fa-check-circle'></i> Complete backup created successfully:<br>
                - Database: {$result['db']['filename']}<br>
                - Files: {$result['files']['filename']} ({$result['files']['file_count']} files)
            </div>";
        } else {
            $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Error creating complete backup</div>";
        }
    }
}

// Restore backup
if (isset($_POST['restore_backup']) && !empty($_POST['backup_file'])) {
    $filename = basename($_POST['backup_file']);
    
    // Determine backup type from filename
    if (strpos($filename, '.sql') !== false) {
        $result = restoreDatabaseBackup($config, $filename);
        if ($result['success']) {
            $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Database restored successfully from: $filename</div>";
        } else {
            $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Error restoring database: {$result['error']}</div>";
        }
    } elseif (strpos($filename, '.zip') !== false) {
        $result = restoreFilesBackup($config, $filename);
        if ($result['success']) {
            $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Files restored successfully from: $filename ({$result['file_count']} files)</div>";
        } else {
            $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Error restoring files: {$result['error']}</div>";
        }
    }
}

// Delete backup
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $filename = basename($_GET['delete']);
    $type = strpos($filename, '.sql') !== false ? 'database' : 'files';
    $dir = $type === 'database' ? $config['db_backup_dir'] : $config['files_backup_dir'];
    $filepath = $dir . $filename;
    
    if (file_exists($filepath) && unlink($filepath)) {
        $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Backup deleted successfully</div>";
    } else {
        $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Error deleting backup</div>";
    }
}

// Download backup
if (isset($_GET['download']) && !empty($_GET['download'])) {
    $filename = basename($_GET['download']);
    $type = strpos($filename, '.sql') !== false ? 'database' : 'files';
    $dir = $type === 'database' ? $config['db_backup_dir'] : $config['files_backup_dir'];
    $filepath = $dir . $filename;
    
    if (file_exists($filepath)) {
        $contentType = $type === 'database' ? 'application/sql' : 'application/zip';
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}

// Run automatic backup check
$autoBackupResult = checkAutomaticBackup($config);
if ($autoBackupResult !== false) {
    $message = "<div class='alert info'><i class='fas fa-clock'></i> Automatic backup was due and has been created successfully.</div>";
}

// Get backup files from both directories
$dbBackups = getBackupFiles($config['db_backup_dir'], 'database');
$fileBackups = getBackupFiles($config['files_backup_dir'], 'files');
$backups = array_merge($dbBackups, $fileBackups);

// Sort by date (newest first)
usort($backups, function($a, $b) {
    return $b['date'] - $a['date'];
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Backup System - Nail Architect Admin</title>
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
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: white;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .form-control:focus {
            outline: none;
            border-color: #D9BBB0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            cursor: pointer;
        }
        
        .form-check input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
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
            position: relative;
            overflow: hidden;
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
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            margin-left: 5px;
        }
        
        .badge-primary {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            background-color: rgba(0,0,0,0.02);
            padding: 5px;
            border-radius: 10px;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-weight: 500;
            text-align: center;
            flex: 1;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .progress-container {
            margin-top: 10px;
            margin-bottom: 20px;
        }
        
        .progress-bar {
            height: 5px;
            background-color: #f0f0f0;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 5px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            width: 0;
            transition: width 0.5s;
        }
        
        .auto-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #fff3cd;
            color: #856404;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .backup-type-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 500;
            margin-right: 8px;
        }
        
        .db-badge {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .files-badge {
            background-color: #d4edda;
            color: #155724;
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
            
            <div class="menu-item active" onclick="window.location.href='backup-system.php'">
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
        <div class="page-title">Enhanced Backup & Restore System</div>
    </div>
    
    <div class="content-wrapper">
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        
        <!-- Tab navigation -->
        <div class="tabs">
            <div class="tab active" onclick="switchTab('backup-tab')">Backup</div>
            <div class="tab" onclick="switchTab('restore-tab')">Restore</div>
            <div class="tab" onclick="switchTab('schedule-tab')">Schedule</div>
        </div>
        
        <!-- Backup Tab -->
        <div id="backup-tab" class="tab-content active">
            <div class="card-grid">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="card-title">Database Backup</div>
                    </div>
                    <div class="card-body">
                        Create a backup of your database only. This will save all your website data including appointments, clients, and system settings.
                    </div>
                    <div class="card-footer">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="backup_type" value="database">
                            <button type="submit" name="create_backup" class="btn btn-primary">
                                <i class="fas fa-database"></i> Backup Database
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon" style="background-color: #d4edda; color: #155724;">
                            <i class="fas fa-file-code"></i>
                        </div>
                        <div class="card-title">Files Backup</div>
                    </div>
                    <div class="card-body">
                        Create a backup of your website files only. This will save all your PHP files, images, CSS, and other website assets.
                    </div>
                    <div class="card-footer">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="backup_type" value="files">
                            <button type="submit" name="create_backup" class="btn btn-primary">
                                <i class="fas fa-file-code"></i> Backup Files
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon" style="background-color: #d1ecf1; color: #0c5460;">
                            <i class="fas fa-copy"></i>
                        </div>
                        <div class="card-title">Complete Backup</div>
                    </div>
                    <div class="card-body">
                        Create a full backup of both your database and website files. This is recommended for complete protection of your website.
                    </div>
                    <div class="card-footer">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="backup_type" value="complete">
                            <button type="submit" name="create_backup" class="btn btn-primary">
                                <i class="fas fa-copy"></i> Complete Backup
                            </button>
                        </form>
                    </div>
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
                                <th>Type</th>
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
                                            <i class="fas <?php echo $backup['type'] === 'database' ? 'fa-database' : 'fa-file-zipper'; ?>" style="color: #666;"></i>
                                            <?php echo htmlspecialchars($backup['name']); ?>
                                            <?php if ($backup['auto']): ?>
                                                <span class="badge badge-warning">Auto</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="backup-type-badge <?php echo $backup['type'] === 'database' ? 'db-badge' : 'files-badge'; ?>">
                                            <?php echo ucfirst($backup['type']); ?>
                                        </span>
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
                        <p>No backups found. Create your first backup to get started.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Restore Tab -->
        <div id="restore-tab" class="tab-content">
            <div class="content-section">
                <div class="section-header">
                    <div class="section-title">Restore Backup</div>
                </div>
                
                <div class="warning-note">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Warning:</strong> Restoring from a backup will replace your current data with the backup version. This action cannot be undone. It's recommended to create a new backup before restoring.
                    </div>
                </div>
                
                <?php if (count($backups) > 0): ?>
                    <form method="post" onsubmit="return confirm('Are you sure? This will replace your current data and cannot be undone.');">
                        <div class="form-group">
                            <label for="backup_file">Select Backup to Restore:</label>
                            <select name="backup_file" id="backup_file" class="form-control" required>
                                <option value="">-- Select Backup File --</option>
                                <?php foreach ($backups as $backup): ?>
                                    <option value="<?php echo htmlspecialchars($backup['name']); ?>">
                                        <?php echo htmlspecialchars($backup['name']); ?> 
                                        (<?php echo ucfirst($backup['type']); ?> - 
                                        <?php echo date('F j, Y g:i a', $backup['date']); ?>) - 
                                        <?php echo number_format($backup['size'] / 1024, 2); ?> KB
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="warning-note" style="background-color: #f8d7da; color: #721c24; border-color: #dc3545;">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Important:</strong> Please ensure no one is using the website during restore process. The website may be temporarily unavailable during restore.
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="restore_backup" class="btn btn-danger">
                                <i class="fas fa-undo"></i> Restore Backup
                            </button>
                            <button type="button" onclick="switchTab('backup-tab')" class="btn btn-info">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-database"></i>
                        <p>No backups found. Create a backup first before attempting to restore.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Schedule Tab -->
        <div id="schedule-tab" class="tab-content">
            <div class="content-section">
                <div class="section-header">
                    <div class="section-title">Automatic Backup Schedule</div>
                </div>
                
                <div class="info-note alert info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        Configure automatic backups to regularly save your website data and files without manual intervention.
                    </div>
                </div>
                
                <form method="post">
                    <div class="form-check">
                        <input type="checkbox" id="auto_backup_enabled" name="auto_backup_enabled" <?php echo $config['auto_backup_enabled'] ? 'checked' : ''; ?>>
                        <label for="auto_backup_enabled">Enable Automatic Backups</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="auto_backup_frequency">Backup Frequency:</label>
                        <select name="auto_backup_frequency" id="auto_backup_frequency" class="form-control">
                            <option value="hourly" <?php echo $config['auto_backup_frequency'] === 'hourly' ? 'selected' : ''; ?>>Hourly</option>
                            <option value="daily" <?php echo $config['auto_backup_frequency'] === 'daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="weekly" <?php echo $config['auto_backup_frequency'] === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="time-setting">
                        <label for="auto_backup_time">Time of Day (Daily/Weekly):</label>
                        <input type="time" name="auto_backup_time" id="auto_backup_time" class="form-control" value="<?php echo $config['auto_backup_time']; ?>">
                    </div>
                    
                    <div class="form-group" id="day-setting">
                        <label for="auto_backup_day">Day of Week (Weekly only):</label>
                        <select name="auto_backup_day" id="auto_backup_day" class="form-control">
                            <option value="Monday" <?php echo $config['auto_backup_day'] === 'Monday' ? 'selected' : ''; ?>>Monday</option>
                            <option value="Tuesday" <?php echo $config['auto_backup_day'] === 'Tuesday' ? 'selected' : ''; ?>>Tuesday</option>
                            <option value="Wednesday" <?php echo $config['auto_backup_day'] === 'Wednesday' ? 'selected' : ''; ?>>Wednesday</option>
                            <option value="Thursday" <?php echo $config['auto_backup_day'] === 'Thursday' ? 'selected' : ''; ?>>Thursday</option>
                            <option value="Friday" <?php echo $config['auto_backup_day'] === 'Friday' ? 'selected' : ''; ?>>Friday</option>
                            <option value="Saturday" <?php echo $config['auto_backup_day'] === 'Saturday' ? 'selected' : ''; ?>>Saturday</option>
                            <option value="Sunday" <?php echo $config['auto_backup_day'] === 'Sunday' ? 'selected' : ''; ?>>Sunday</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="auto_backup_keep">Number of Automatic Backups to Keep:</label>
                        <input type="number" name="auto_backup_keep" id="auto_backup_keep" class="form-control" value="<?php echo $config['auto_backup_keep']; ?>" min="1" max="30">
                        <small style="color: #666;">Older automatic backups will be deleted automatically.</small>
                    </div>
                    
                    <div class="card" style="margin-top: 20px; margin-bottom: 20px;">
                        <div class="card-header">
                            <div class="card-icon" style="background-color: #d1ecf1; color: #0c5460;">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="card-title">Automatic Backup Information</div>
                        </div>
                        <div class="card-body">
                            <p>The system will automatically create both database and file backups according to your schedule. These backups are stored separately from manual backups.</p>
                            <div class="progress-container">
                                <div style="display: flex; justify-content: space-between; font-size: 12px; color: #666;">
                                    <span>Total Storage Used by Backups:</span>
                                    <span id="storage-used">
                                        <?php
                                            $totalSize = 0;
                                            foreach ($backups as $backup) {
                                                $totalSize += $backup['size'];
                                            }
                                            echo number_format($totalSize / (1024 * 1024), 2);
                                        ?> MB
                                    </span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo min(100, $totalSize / (50 * 1024 * 1024) * 100); ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="save_schedule" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Schedule Settings
                    </button>
                </form>
            </div>
            
            <div class="content-section">
                <div class="section-header">
                    <div class="section-title">Automatic Backup History</div>
                </div>
                
                <?php
                // Filter only automatic backups
                $autoBackups = array_filter($backups, function($backup) {
                    return $backup['auto'] === true;
                });
                
                // Check if auto backup is running
                $isBackupRunning = file_exists(BACKUP_LOCK_FILE) && (time() - filemtime(BACKUP_LOCK_FILE) < 3600);
                if ($isBackupRunning): 
                ?>
                    <div class="alert info" style="display: flex; align-items: center;">
                        <div style="margin-right: 20px;"><i class="fas fa-sync fa-spin"></i></div>
                        <div>
                            <strong>Automatic backup in progress...</strong><br>
                            A backup is currently running in the background. Refresh the page in a few minutes to see the new backup.
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (count($autoBackups) > 0): ?>
                    <table class="backup-table">
                        <thead>
                            <tr>
                                <th>Backup File</th>
                                <th>Type</th>
                                <th>Created Date</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($autoBackups as $backup): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <i class="fas <?php echo $backup['type'] === 'database' ? 'fa-database' : 'fa-file-zipper'; ?>" style="color: #666;"></i>
                                            <?php echo htmlspecialchars($backup['name']); ?>
                                            <span class="badge badge-warning">Auto</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="backup-type-badge <?php echo $backup['type'] === 'database' ? 'db-badge' : 'files-badge'; ?>">
                                            <?php echo ucfirst($backup['type']); ?>
                                        </span>
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
                        <i class="fas fa-clock"></i>
                        <p>No automatic backups have been created yet. They will appear here once the schedule runs.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching function
        function switchTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to clicked tab button
            Array.from(document.querySelectorAll('.tab')).find(tab => {
                return tab.getAttribute('onclick').includes(tabId);
            }).classList.add('active');
        }
        
        // Show/hide time and day settings based on frequency
        document.getElementById('auto_backup_frequency').addEventListener('change', function() {
            const frequency = this.value;
            const timeSettings = document.getElementById('time-setting');
            const daySettings = document.getElementById('day-setting');
            
            if (frequency === 'hourly') {
                timeSettings.style.display = 'none';
                daySettings.style.display = 'none';
            } else if (frequency === 'daily') {
                timeSettings.style.display = 'block';
                daySettings.style.display = 'none';
            } else if (frequency === 'weekly') {
                timeSettings.style.display = 'block';
                daySettings.style.display = 'block';
            }
        });
        
        // Trigger change event on page load
        document.addEventListener('DOMContentLoaded', function() {
            const frequencySelect = document.getElementById('auto_backup_frequency');
            if (frequencySelect) {
                const event = new Event('change');
                frequencySelect.dispatchEvent(event);
            }
        });
    </script>
</body>
</html>