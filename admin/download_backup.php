<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges
requireAdmin('admin_level3');

// Initialize
$logger = new Logger();

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

// Log the download
$logger->logAction($_SESSION['user_id'], 'download_backup', "Downloaded backup file: $filename");

// Set headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Clear output buffer
ob_clean();
flush();

// Output file
readfile($filePath);
exit; 