<?php
// Start session
session_start();

// Define backup directory
$backup_dir = "backups/";
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Define directories to backup
$media_dirs = [
    'uploads/',
    'Assets/'
];

// Create timestamp for filename
$timestamp = date("Y-m-d-H-i-s");
$zip_filename = $backup_dir . "nail_architect_media_" . $timestamp . ".zip";

// Create ZIP archive
$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE) !== TRUE) {
    $_SESSION['backup_message'] = "<div class='alert error'>Cannot create zip file</div>";
    header('Location: admin-backup.php');
    exit;
}

// Add files to the zip
$files_added = 0;
foreach ($media_dirs as $dir) {
    if (file_exists($dir)) {
        $files_added += addDirToZip($zip, $dir, $dir);
    }
}

// Close the zip file
$zip->close();

if ($files_added > 0) {
    $_SESSION['backup_message'] = "<div class='alert success'>Media backup created successfully: " . basename($zip_filename) . " ($files_added files)</div>";
} else {
    if (file_exists($zip_filename)) {
        unlink($zip_filename);
    }
    $_SESSION['backup_message'] = "<div class='alert error'>No media files found to backup</div>";
}

// Redirect back to backup page
header('Location: admin-backup.php');
exit;

// Function to add a directory to a zip recursively
function addDirToZip($zip, $dir, $zipDir) {
    $files_added = 0;
    if (is_dir($dir)) {
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != "." && $file != "..") {
                    $filePath = $dir . '/' . $file;
                    $zipFilePath = $zipDir . '/' . $file;
                    
                    // If it's a directory, recursively add it
                    if (is_dir($filePath)) {
                        $zip->addEmptyDir($zipFilePath);
                        $files_added += addDirToZip($zip, $filePath, $zipFilePath);
                    } else {
                        // Add file to zip
                        if ($zip->addFile($filePath, $zipFilePath)) {
                            $files_added++;
                        }
                    }
                }
            }
            closedir($handle);
        }
    }
    return $files_added;
}
?>
