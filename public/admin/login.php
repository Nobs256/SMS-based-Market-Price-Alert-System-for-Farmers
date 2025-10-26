<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\AuthService;

session_start();

$authService = new AuthService();

// If already logged in, redirect to the dashboard
if ($authService->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($authService->login($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $_SESSION['status'] = ['message' => 'Invalid username or password.', 'type' => 'error'];
        header('Location: login.php');
        exit;
    }
}

require_once __DIR__ . '/../../templates/admin/login.phtml';