<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges
requireAdmin('admin_level3');

// Initialize
$logger = new Logger();

// Check CSRF token
if (!isset($_GET['csrf_token']) || !checkCsrfToken($_GET['csrf_token'])) {
    setFlashMessage('danger', 'Invalid request. Please try again.');
    redirect('backup.php');
    exit;
}

// Check if file parameter is provided
if (!isset($_GET['file']) || empty($_GET['file'])) {
    setFlashMessage('danger', 'No file specified.');
    redirect('backup.php');
    exit;
}

// Clean the filename to prevent directory traversal
$filename = basename($_GET['file']);
$backupDir = __DIR__ . '/../backups';
$filePath = $backupDir . '/' . $filename;

// Check if file exists
if (!file_exists($filePath)) {
    setFlashMessage('danger', 'File not found.');
    redirect('backup.php');
    exit;
}

// Delete the file
if (unlink($filePath)) {
    // Log the action
    $logger->logAction($_SESSION['user_id'], 'delete_backup', "Deleted backup file: $filename");
    
    setFlashMessage('success', "Backup file '$filename' has been deleted.");
} else {
    setFlashMessage('danger', "Failed to delete backup file '$filename'.");
}

// Redirect back to backup page
redirect('backup.php'); 