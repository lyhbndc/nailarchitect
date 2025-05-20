<?php
return array (
  'db_host' => 'localhost',
  'db_user' => 'root',
  'db_pass' => '',
  'db_name' => 'nail_architect_db',
  'backup_dir' => 'D:\\xampp\\htdocs\\nailarchitect/backups/',
  'db_backup_dir' => 'D:\\xampp\\htdocs\\nailarchitect/backups/database/',
  'files_backup_dir' => 'D:\\xampp\\htdocs\\nailarchitect/backups/files/',
  'auto_backup_enabled' => true,
  'auto_backup_frequency' => 'daily',
  'auto_backup_time' => '03:00',
  'auto_backup_day' => 'Sunday',
  'auto_backup_keep' => 7,
  'max_backups' => 10,
  'website_root' => 'D:\\xampp\\htdocs\\nailarchitect',
  'exclude_paths' => 
  array (
    0 => '/backups/',
    1 => '/node_modules/',
    2 => '/.git/',
    3 => '/.env',
  ),
);
?>