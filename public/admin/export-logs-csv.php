<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\AuthService;
use App\LogService;

session_start();

// --- Authentication Check ---
$authService = new AuthService();
if (!$authService->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$logService = new LogService();
$logs = $logService->getAllLogs();

// Set headers to force download as CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=System_Broadcast_Logs_' . date('Y-m-d') . '.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Output column headers
fputcsv($output, ['Date & Time', 'Status', 'Message Details']);

// Output data rows
foreach ($logs as $log) {
    fputcsv($output, [
        $log['created_at'],
        ucfirst($log['status']),
        $log['message']
    ]);
}

fclose($output);
exit;