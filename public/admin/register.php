<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\AuthService;

session_start();

$authService = new AuthService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($password !== $passwordConfirm) {
        $_SESSION['status'] = ['message' => 'Passwords do not match.', 'type' => 'error'];
    } elseif (empty($username) || empty($password)) {
        $_SESSION['status'] = ['message' => 'Username and password cannot be empty.', 'type' => 'error'];
    } else {
        if ($authService->register($username, $password)) {
            $_SESSION['status'] = ['message' => 'Registration successful! Please log in.', 'type' => 'success'];
            header('Location: login.php');
            exit;
        } else {
            $_SESSION['status'] = ['message' => 'Registration failed. The username might already be taken.', 'type' => 'error'];
        }
    }

    header('Location: register.php');
    exit;
}

require_once __DIR__ . '/../../templates/admin/register.phtml';