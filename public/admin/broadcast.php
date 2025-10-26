<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\AuthService;
use App\BroadcastService;

session_start();

// --- Authentication Check ---
$authService = new AuthService();
if (!$authService->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Only proceed if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['broadcast_now'])) {
    $broadcastService = new BroadcastService();
    $result = $broadcastService->executeBroadcast();

    // Standardize the session status array
    $_SESSION['status'] = [
        'message' => $result['message'],
        'type' => $result['success'] ? 'success' : 'error'
    ];
}

header('Location: dashboard.php?page=home');
exit;