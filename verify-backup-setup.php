<?php
// verify-backup-setup.php - Diagnostic script for backup system
echo "<h2>Backup System Diagnostic</h2>";

// 1. Check PHP version and extensions
echo "<h3>1. PHP Configuration</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "MySQLi Extension: " . (extension_loaded('mysqli') ? '✓ Installed' : '✗ Not installed') . "<br>";
echo "Zip Extension: " . (extension_loaded('zip') ? '✓ Installed' : '✗ Not installed') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . " seconds<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br><br>";

// 2. Check database connection
echo "<h3>2. Database Connection</h3>";
$conn = @mysqli_connect("localhost", "root", "", "nail_architect_db");
if ($conn) {
    echo "✓ Database connection successful<br>";
    mysqli_close($conn);
} else {
    echo "✗ Database connection failed: " . mysqli_connect_error() . "<br>";
}
echo "<br>";

// 3. Check directory permissions
echo "<h3>3. Directory Permissions</h3>";
$dirs_to_check = [
    __DIR__ . "/backups/",
    __DIR__ . "/backups/media/",
    __DIR__ . "/uploads/",
    __DIR__ . "/uploads/inspirations/",
    __DIR__ . "/uploads/messages/",
    __DIR__ . "/uploads/payments/"
];

foreach ($dirs_to_check as $dir) {
    if (!file_exists($dir)) {
        if (@mkdir($dir, 0755, true)) {
            echo "✓ Created: $dir<br>";
        } else {
            echo "✗ Cannot create: $dir<br>";
        }
    } else {
        echo "✓ Exists: $dir ";
        echo (is_writable($dir) ? '(Writable)' : '(Not writable)') . "<br>";
    }
}
echo "<br>";

// 4. Test file creation
echo "<h3>4. File Creation Test</h3>";
$test_file = __DIR__ . "/backups/test_" . time() . ".txt";
if (@file_put_contents($test_file, "test")) {
    echo "✓ Can create files in backup directory<br>";
    unlink($test_file);
} else {
    echo "✗ Cannot create files in backup directory<br>";
}
echo "<br>";

// 5. Check required files
echo "<h3>5. Required Files Check</h3>";
$required_files = [
    'admin-backup.php',
    'create-media-backup.php',
    'download-backup.php',
    'delete-backup.php',
    'restore-backup.php'
];

foreach ($required_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✓ Found: $file<br>";
    } else {
        echo "✗ Missing: $file<br>";
    }
}
echo "<br>";

// 6. Session test
echo "<h3>6. Session Test</h3>";
session_start();
$_SESSION['test'] = 'works';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'works') {
    echo "✓ Sessions are working<br>";
    unset($_SESSION['test']);
} else {
    echo "✗ Sessions are not working properly<br>";
}
echo "<br>";

// 7. Recommendations
echo "<h3>7. Recommendations</h3>";
echo "<ul>";
echo "<li>Ensure all directories have proper permissions (755 or 777)</li>";
echo "<li>Install missing PHP extensions if any</li>";
echo "<li>Verify database credentials in all PHP files</li>";
echo "<li>Check PHP error logs for specific errors</li>";
echo "<li>Consider increasing max_execution_time for large databases</li>";
echo "</ul>";

// 8. Quick fixes
echo "<h3>8. Quick Fix Commands</h3>";
echo "<pre>";
echo "# Set directory permissions (run in terminal):\n";
echo "chmod -R 755 backups/\n";
echo "chmod -R 755 uploads/\n\n";

echo "# Create missing directories:\n";
echo "mkdir -p backups/media\n";
echo "mkdir -p uploads/inspirations\n";
echo "mkdir -p uploads/messages\n";
echo "mkdir -p uploads/payments\n";
echo "</pre>";
?>