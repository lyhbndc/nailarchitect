<?php
// Start session and check admin authentication
session_start();

// Define directories to backup
$directories_to_backup = [
    'uploads/inspirations' => 'inspirations',
    'uploads/messages' => 'messages',  
    'uploads/payments' => 'payments'
];

// Check if at least one directory exists
$has_directories = false;
foreach ($directories_to_backup as $dir => $name) {
    if (is_dir($dir)) {
        $has_directories = true;
        break;
    }
}

if (!$has_directories) {
    $_SESSION['backup_message'] = "<div class='alert error'>No upload directories found. Please ensure upload directories exist.</div>";
    header("Location: admin-backup.php");
    exit();
}

// Create backups directory if it doesn't exist
$backup_dir = "backups/";
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Create media backups directory
$media_backup_dir = $backup_dir . "media/";
if (!file_exists($media_backup_dir)) {
    mkdir($media_backup_dir, 0755, true);
}

// Generate zip filename with timestamp
$timestamp = date("Y-m-d-H-i-s");
$zip_filename = $media_backup_dir . "media_backup_" . $timestamp . ".zip";

// Create ZIP archive
$zip = new ZipArchive();
$result = $zip->open($zip_filename, ZipArchive::CREATE);

if ($result === TRUE) {
    $file_count = 0;
    // Add files to ZIP
    foreach ($directories_to_backup as $source_dir => $folder_name) {
        if (is_dir($source_dir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source_dir),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);
                
                // Ignore "." and ".." directories
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                    continue;
                }
                
                $file_real_path = realpath($file);
                
                if (is_dir($file_real_path)) {
                    // Add directory to ZIP
                    $zip->addEmptyDir(str_replace($source_dir . '/', $folder_name . '/', $file . '/'));
                } else if (is_file($file_real_path)) {
                    // Add file to ZIP
                    $zip->addFromString(
                        str_replace($source_dir . '/', $folder_name . '/', $file),
                        file_get_contents($file_real_path)
                    );
                    $file_count++;
                }
            }
        }
    }
    
    // Close ZIP archive
    $zip->close();
    
    // Set success message
    $_SESSION['backup_message'] = "<div class='alert success'>Media backup created successfully: " . basename($zip_filename) . " (Size: " . round(filesize($zip_filename) / 1024 / 1024, 2) . " MB) - {$file_count} files added</div>";
} else {
    // More detailed error message
    $error_msg = "Unknown error";
    switch($result) {
        case ZipArchive::ER_OK: $error_msg = "No error"; break;
        case ZipArchive::ER_MULTIDISK: $error_msg = "Multi-disk zip archives not supported"; break;
        case ZipArchive::ER_RENAME: $error_msg = "Renaming temporary file failed"; break;
        case ZipArchive::ER_CLOSE: $error_msg = "Closing zip archive failed"; break;
        case ZipArchive::ER_SEEK: $error_msg = "Seek error"; break;
        case ZipArchive::ER_READ: $error_msg = "Read error"; break;
        case ZipArchive::ER_WRITE: $error_msg = "Write error"; break;
        case ZipArchive::ER_CRC: $error_msg = "CRC error"; break;
        case ZipArchive::ER_ZIPCLOSED: $error_msg = "Containing zip archive was closed"; break;
        case ZipArchive::ER_NOENT: $error_msg = "No such file"; break;
        case ZipArchive::ER_EXISTS: $error_msg = "File already exists"; break;
        case ZipArchive::ER_OPEN: $error_msg = "Can't open file"; break;
        case ZipArchive::ER_TMPOPEN: $error_msg = "Failure to create temporary file"; break;
        case ZipArchive::ER_ZLIB: $error_msg = "Zlib error"; break;
        case ZipArchive::ER_MEMORY: $error_msg = "Memory allocation failure"; break;
        case ZipArchive::ER_CHANGED: $error_msg = "Entry has been changed"; break;
        case ZipArchive::ER_COMPNOTSUPP: $error_msg = "Compression method not supported"; break;
        case ZipArchive::ER_EOF: $error_msg = "Premature EOF"; break;
        case ZipArchive::ER_INVAL: $error_msg = "Invalid argument"; break;
        case ZipArchive::ER_NOZIP: $error_msg = "Not a zip archive"; break;
        case ZipArchive::ER_INTERNAL: $error_msg = "Internal error"; break;
        case ZipArchive::ER_INCONS: $error_msg = "Zip archive inconsistent"; break;
        case ZipArchive::ER_REMOVE: $error_msg = "Can't remove file"; break;
        case ZipArchive::ER_DELETED: $error_msg = "Entry has been deleted"; break;
    }
    $_SESSION['backup_message'] = "<div class='alert error'>Error creating media backup: {$error_msg}. Please check directory permissions.</div>";
}

// Redirect back to admin backup page
header("Location: admin-backup.php");
exit();
?>