<?php
require_once '../../vendor/autoload.php';
require_once '../../src/Database.php';
require_once '../../src/FarmerAuthService.php';

use App\FarmerAuthService;

session_start();
$auth = new FarmerAuthService();

if ($auth->isFarmerLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone_number'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($auth->login($phone, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid phone number or password. If you are a new farmer, please contact the administrator to set up your account.';
    }
}

$pageTitle = "Farmer Login - Rubanda Potato Hub";
include '../../templates/partials/header.phtml';
include '../../templates/public/login.phtml';
include '../../templates/partials/footer.phtml';