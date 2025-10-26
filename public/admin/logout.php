<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\AuthService;

session_start();
$authService = new AuthService();
$authService->logout();

header('Location: login.php');
exit;