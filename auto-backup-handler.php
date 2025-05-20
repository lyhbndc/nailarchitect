<?php
// auto-backup-handler.php - Manages automatic backups without using cron
// Include this file at the top of your admin panel pages

// Prevent direct access
if (!defined('ADMIN_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Define constants
define('BACKUP_CONFIG_FILE', __DIR__ . '/backup-config.php');
define('BACKUP_LOCK_FILE', __DIR__ . '/backups/backup.lock');
define('BACKUP_LOG_FILE', __DIR__ . '/backups/logs/backup.log');
define('LAST_RUN_FILE', __DIR__ . '/backups/last_auto_backup.txt');

// Make sure log directory exists
if (!file_exists(dirname(BACKUP_LOG_FILE))) {
    @mkdir(dirname(BACKUP_LOG_FILE), 0755, true);
}

// Function checks if necessary functions already exist
if (!function_exists('backup_log')) {
    // Log function
    function backup_log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        @file_put_contents(BACKUP_LOG_FILE, $logMessage, FILE_APPEND);
    }
}

// Check if backup config exists
if (!file_exists(BACKUP_CONFIG_FILE)) {
    // Create default config if it doesn't exist
    $defaultConfig = [
        // Database settings
        'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
        'db_user' => $_ENV['DB_USER'] ?? 'root',
        'db_pass' => $_ENV['DB_PASS'] ?? '',
        'db_name' => $_ENV['DB_NAME'] ?? 'nail_architect_db',
        
        // Backup directories
        'backup_dir' => __DIR__ . '/backups/',
        'db_backup_dir' => __DIR__ . '/backups/database/',
        'files_backup_dir' => __DIR__ . '/backups/files/',
        
        // Automatic backup settings
        'auto_backup_enabled' => true,
        'auto_backup_frequency' => 'daily', 
        'auto_backup_time' => '03:00',
        'auto_backup_day' => 'Sunday',
        'auto_backup_keep' => 7,
        
        // Retention settings
        'max_backups' => 10,
        
        // Website root directory to backup (excluding backup directory)
        'website_root' => __DIR__,
        
        // Directories/files to exclude from website backup
        'exclude_paths' => [
            '/backups/',
            '/node_modules/',
            '/.git/',
            '/.env',
        ],
    ];
    
    @file_put_contents(BACKUP_CONFIG_FILE, "<?php\nreturn " . var_export($defaultConfig, true) . ";\n?>");
    backup_log("Created default backup configuration");
}

// Function to check if it's time to run a backup
if (!function_exists('shouldRunBackup')) {
    function shouldRunBackup() {
        // Don't run backups for non-admin users
        if (!isset($_SESSION['admin_id'])) {
            return false;
        }
        
        // Check if a backup is already in progress
        if (file_exists(BACKUP_LOCK_FILE) && (time() - filemtime(BACKUP_LOCK_FILE)) < 3600) {
            // Backup lock exists and is less than 1 hour old
            return false;
        }
        
        // Load configuration
        $config = require BACKUP_CONFIG_FILE;
        
        // Check if automatic backups are enabled
        if (!$config['auto_backup_enabled']) {
            return false;
        }
        
        // Get last automatic backup time
        $lastRun = file_exists(LAST_RUN_FILE) ? intval(file_get_contents(LAST_RUN_FILE)) : 0;
        $currentTime = time();
        
        // Only check occasionally (10% chance per admin page load) to avoid constant checking
        if (rand(1, 10) > 1 && $lastRun > 0) {
            return false;
        }
        
        switch ($config['auto_backup_frequency']) {
            case 'hourly':
                // Run if last backup was more than 1 hour ago
                return ($currentTime - $lastRun) >= 3600;
                
            case 'daily':
                // Parse scheduled time
                list($hour, $minute) = explode(':', $config['auto_backup_time']);
                $scheduledTime = strtotime(date('Y-m-d') . ' ' . $hour . ':' . $minute . ':00');
                
                // If scheduled time has passed today and no backup today
                $lastRunDate = date('Y-m-d', $lastRun);
                $today = date('Y-m-d');
                
                return ($today > $lastRunDate && $currentTime >= $scheduledTime);
                
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
                    return ($lastRunDay != $scheduledDay && $currentTime >= $scheduledTime);
                }
                return false;
        }
        
        return false;
    }
}

// Function to run a background backup process
if (!function_exists('runBackgroundBackup')) {
    function runBackgroundBackup() {
        // Create a lock file to prevent multiple backup processes
        @file_put_contents(BACKUP_LOCK_FILE, time());
        
        // Set low time limit and flush buffers to let the page load normally
        if (function_exists('set_time_limit')) {
            set_time_limit(5);
        }
        ob_flush();
        flush();
        
        // Close the current session to allow other scripts to run
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        
        // Wait a short time to let the main page finish loading
        usleep(500000); // 0.5 seconds
        
        try {
            // Load configuration
            $config = require BACKUP_CONFIG_FILE;
            
            // Create necessary directories
            foreach (['backup_dir', 'db_backup_dir', 'files_backup_dir'] as $dir) {
                if (!file_exists($config[$dir])) {
                    @mkdir($config[$dir], 0755, true);
                }
            }
            
            backup_log("Starting automatic background backup...");
            
            // Run database backup
            $dbResult = createBackgroundDatabaseBackup($config);
            
            // Run files backup if database backup was successful
            $filesResult = false;
            if ($dbResult) {
                $filesResult = createBackgroundFilesBackup($config);
            }
            
            // Update last run time
            @file_put_contents(LAST_RUN_FILE, time());
            
            // Log results
            if ($dbResult && $filesResult) {
                backup_log("Automatic background backup completed successfully");
            } else {
                backup_log("Automatic background backup completed with errors");
            }
        } catch (Exception $e) {
            backup_log("ERROR in background backup: " . $e->getMessage());
        }
        
        // Remove lock file
        @unlink(BACKUP_LOCK_FILE);
    }
}

// Function to create a database backup in the background
if (!function_exists('createBackgroundDatabaseBackup')) {
    function createBackgroundDatabaseBackup($config) {
        try {
            backup_log("Creating database backup...");
            
            // Connect to database
            $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
            if ($conn->connect_error) {
                backup_log("Database connection failed: " . $conn->connect_error);
                return false;
            }
            
            $filename = 'auto_db_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $config['db_backup_dir'] . $filename;
            
            // Open file for writing
            $file = @fopen($filepath, 'w');
            if (!$file) {
                backup_log("Cannot open file for writing: $filepath");
                return false;
            }
            
            // Write header
            fwrite($file, "-- Database Backup (Automatic)\n");
            fwrite($file, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($file, "-- Database: " . $config['db_name'] . "\n\n");
            fwrite($file, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($file, "SET time_zone = \"+00:00\";\n\n");
            
            // Get all tables
            $tables = array();
            $result = $conn->query("SHOW TABLES");
            while ($row = $result->fetch_row()) {
                $tables[] = $row[0];
            }
            
            // Export each table
            foreach ($tables as $table) {
                fwrite($file, "\n-- Table structure for table `$table`\n");
                fwrite($file, "DROP TABLE IF EXISTS `$table`;\n");
                
                $result = $conn->query("SHOW CREATE TABLE `$table`");
                $row = $result->fetch_row();
                fwrite($file, $row[1] . ";\n\n");
                
                // Get data
                $result = $conn->query("SELECT * FROM `$table`");
                if ($result->num_rows > 0) {
                    fwrite($file, "-- Data for table `$table`\n");
                    while ($row = $result->fetch_assoc()) {
                        $values = array_map(function($value) use ($conn) {
                            return $value === null ? 'NULL' : "'" . $conn->real_escape_string($value) . "'";
                        }, $row);
                        fwrite($file, "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n");
                        
                        // Flush occasionally to prevent memory buildup
                        if ($result->num_rows > 1000 && rand(0, 50) === 0) {
                            fflush($file);
                        }
                    }
                }
                fwrite($file, "\n");
            }
            
            // Close file and database connection
            fclose($file);
            $conn->close();
            
            // Clean up old backups
            cleanOldAutoBackups($config['db_backup_dir'], $config['auto_backup_keep'], 'auto_db_');
            
            backup_log("Database backup created successfully: $filename (" . round(filesize($filepath) / 1024, 2) . " KB)");
            return true;
        } catch (Exception $e) {
            backup_log("ERROR creating database backup: " . $e->getMessage());
            return false;
        }
    }
}

// Function to create a files backup in the background
if (!function_exists('createBackgroundFilesBackup')) {
    function createBackgroundFilesBackup($config) {
        try {
            backup_log("Creating files backup...");
            
            $filename = 'auto_files_backup_' . date('Y-m-d_H-i-s') . '.zip';
            $filepath = $config['files_backup_dir'] . $filename;
            
            // Check if ZipArchive is available
            if (!class_exists('ZipArchive')) {
                // ZipArchive not available, use tar or directory copy as fallback
                backup_log("ZipArchive is not available. Using alternative backup method.");
                return createFallbackFilesBackup($config);
            }
            
            // Create new ZIP archive
            $zip = new ZipArchive();
            if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                backup_log("Cannot create ZIP archive");
                return false;
            }
            
            // Get real path for website root
            $rootPath = realpath($config['website_root']);
            
            // Initialize important file list (faster than backing up everything)
            $importantFiles = [
                '/*.php',              // PHP files in root
                '/css/*.css',          // CSS files
                '/js/*.js',            // JavaScript files
                '/images/*.*',         // Images
                '/Assets/**/*',         // Assets folder
                '/backups/*.php',      // Only PHP files in backups (not the backups themselves)
                '/fpdf/**/*',          // fpdf folder
                '/lib/**/*',           // lib folder
                '/phpmailer/**/*',     // phpmailer folder  
                '/uploads/**/*',       // uploads folder
                '/inspirations/**/*',  // inspirations folder
                '/messages/**/*',      // messages folder
                '/payments/**/*',      // payments folder
                '/includes/**/*',      // includes folder
                '/admin/**/*',         // all admin files and subfolders
            ];
            
            // Add each important file pattern
            $fileCount = 0;
            foreach ($importantFiles as $pattern) {
                // Handle ** wildcard pattern for recursive subdirectories
                if (strpos($pattern, '**') !== false) {
                    $basePath = str_replace('**/*', '', $pattern);
                    $fullBasePath = $rootPath . $basePath;
                    
                    // Check if directory exists
                    if (is_dir($fullBasePath)) {
                        // Recursively add files from this directory
                        $dirIterator = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($fullBasePath),
                            RecursiveIteratorIterator::LEAVES_ONLY
                        );
                        
                        foreach ($dirIterator as $file) {
                            if ($file->isFile()) {
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
                                
                                // Add file to ZIP
                                $zip->addFile($file, $relativePath);
                                $fileCount++;
                            }
                        }
                    }
                } else {
                    // Handle regular glob patterns
                    $files = glob($rootPath . $pattern, GLOB_BRACE);
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            $relativePath = substr($file, strlen($rootPath) + 1);
                            
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
                            
                            // Add file to ZIP
                            $zip->addFile($file, $relativePath);
                            $fileCount++;
                        }
                    }
                }
            }
            
            // Close ZIP file
            $zip->close();
            
            // Check if zip was created successfully
            if (!file_exists($filepath) || filesize($filepath) < 100) {
                backup_log("ZIP file creation failed or file is too small");
                return false;
            }
            
            // Clean up old backups
            cleanOldAutoBackups($config['files_backup_dir'], $config['auto_backup_keep'], 'auto_files_');
            
            backup_log("Files backup created successfully: $filename (" . round(filesize($filepath) / 1024, 2) . " KB, $fileCount files)");
            return true;
        } catch (Exception $e) {
            backup_log("ERROR creating files backup: " . $e->getMessage());
            return false;
        }
    }
}

// Fallback function to create files backup without ZipArchive
if (!function_exists('createFallbackFilesBackup')) {
    function createFallbackFilesBackup($config) {
        try {
            // Create a timestamp-based folder for this backup
            $timestamp = date('Y-m-d_H-i-s');
            $backupDir = $config['files_backup_dir'] . 'files_backup_' . $timestamp;
            
            // Create backup directory
            if (!@mkdir($backupDir, 0755, true)) {
                backup_log("Cannot create backup directory");
                return false;
            }
            
            // Get real path for website root
            $rootPath = realpath($config['website_root']);
            
            // Initialize important file list (same as in the ZIP version)
            $importantFiles = [
                '/*.php',              // PHP files in root
                '/css/*.css',          // CSS files
                '/js/*.js',            // JavaScript files
                '/images/*.*',         // Images
                '/Assets/**/*',         // Assets folder
                '/backups/*.php',      // Only PHP files in backups (not the backups themselves)
                '/fpdf/**/*',          // fpdf folder
                '/lib/**/*',           // lib folder
                '/phpmailer/**/*',     // phpmailer folder  
                '/uploads/**/*',       // uploads folder
                '/inspirations/**/*',  // inspirations folder
                '/messages/**/*',      // messages folder
                '/payments/**/*',      // payments folder
                '/includes/**/*',      // includes folder
                '/admin/**/*',         // all admin files and subfolders
            ];
            
            // Add each important file pattern
            $fileCount = 0;
            foreach ($importantFiles as $pattern) {
                // Handle ** wildcard pattern for recursive subdirectories
                if (strpos($pattern, '**') !== false) {
                    $basePath = str_replace('**/*', '', $pattern);
                    $fullBasePath = $rootPath . $basePath;
                    
                    // Check if directory exists
                    if (is_dir($fullBasePath)) {
                        // Recursively add files from this directory
                        $dirIterator = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($fullBasePath),
                            RecursiveIteratorIterator::LEAVES_ONLY
                        );
                        
                        foreach ($dirIterator as $file) {
                            if ($file->isFile()) {
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
                                
                                // Create target directory if it doesn't exist
                                $targetDir = dirname($backupDir . '/' . $relativePath);
                                if (!is_dir($targetDir)) {
                                    @mkdir($targetDir, 0755, true);
                                }
                                
                                // Copy file
                                if (@copy($filePath, $backupDir . '/' . $relativePath)) {
                                    $fileCount++;
                                }
                            }
                        }
                    }
                } else {
                    // Handle regular glob patterns
                    $files = glob($rootPath . $pattern, GLOB_BRACE);
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            $relativePath = substr($file, strlen($rootPath) + 1);
                            
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
                            
                            // Create target directory if it doesn't exist
                            $targetDir = dirname($backupDir . '/' . $relativePath);
                            if (!is_dir($targetDir)) {
                                @mkdir($targetDir, 0755, true);
                            }
                            
                            // Copy file
                            if (@copy($file, $backupDir . '/' . $relativePath)) {
                                $fileCount++;
                            }
                        }
                    }
                }
            }
            
            // Create a manifest file with backup information
            $manifestContent = "Backup Date: " . date('Y-m-d H:i:s') . "\n";
            $manifestContent .= "Files Backed Up: " . $fileCount . "\n";
            $manifestContent .= "Website Root: " . $rootPath . "\n";
            @file_put_contents($backupDir . '/backup-manifest.txt', $manifestContent);
            
            // Create a simple text file to record the backup
            $logFile = $config['files_backup_dir'] . 'auto_files_backup_' . $timestamp . '.txt';
            $logContent = "Files backup created at: " . date('Y-m-d H:i:s') . "\n";
            $logContent .= "Backup location: " . $backupDir . "\n";
            $logContent .= "Total files backed up: " . $fileCount . "\n";
            @file_put_contents($logFile, $logContent);
            
            // Clean up old backups
            cleanOldAutoBackups($config['files_backup_dir'], $config['auto_backup_keep'], 'auto_files_');
            
            backup_log("Files backup created successfully without ZIP (directory copy): " . basename($backupDir) . " ($fileCount files)");
            return true;
        } catch (Exception $e) {
            backup_log("ERROR creating fallback files backup: " . $e->getMessage());
            return false;
        }
    }
}
if (!function_exists('cleanOldAutoBackups')) {
    function cleanOldAutoBackups($dir, $keep, $prefix = '') {
        try {
            $files = glob($dir . $prefix . '*.*');
            
            if (count($files) <= $keep) {
                return;
            }
            
            backup_log("Cleaning up old backups, keeping newest $keep...");
            
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $toDelete = array_slice($files, $keep);
            foreach ($toDelete as $file) {
                if (@unlink($file)) {
                    backup_log("Deleted old backup: " . basename($file));
                } else {
                    backup_log("Failed to delete old backup: " . basename($file));
                }
            }
        } catch (Exception $e) {
            backup_log("ERROR cleaning old backups: " . $e->getMessage());
        }
    }
}

// Only run for admin users to avoid unnecessary checks
if (isset($_SESSION['admin_id'])) {
    // Check if a backup should run
    if (shouldRunBackup()) {
        // Run the backup process in the background
        // using register_shutdown_function to not delay page load
        register_shutdown_function('runBackgroundBackup');
    }
}