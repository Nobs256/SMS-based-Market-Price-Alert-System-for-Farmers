<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\AuthService;
use App\FarmerService;

session_start();

// --- Authentication Check ---
$authService = new AuthService();
if (!$authService->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$farmerService = new FarmerService();
$farmerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$farmer = null;

if (!$farmerId || !($farmer = $farmerService->getFarmerById($farmerId))) {
    $_SESSION['status'] = ['message' => 'Error: Invalid or non-existent farmer ID.', 'type' => 'error'];
    header('Location: dashboard.php');
    exit;
}

// --- Handle Form Submission for Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $names = trim($_POST['names'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    $lang = trim($_POST['language'] ?? 'en');

    if (!empty($names) && !empty($phone)) {
        if ($farmerService->updateFarmer($farmerId, $names, $phone, $lang)) {
            $_SESSION['status'] = ['message' => "Farmer '{$names}' updated successfully.", 'type' => 'success'];
            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['status'] = ['message' => 'Error: Could not update farmer. The phone number might already be in use.', 'type' => 'error'];
        }
    } else {
        $_SESSION['status'] = ['message' => 'Error: Farmer name and phone number cannot be empty.', 'type' => 'error'];
    }
    // Redirect back to the edit page to show the error
    header('Location: edit-farmer.php?id=' . $farmerId);
    exit;
}

// Include the view for displaying the form
require_once __DIR__ . '/../../templates/admin/edit-farmer.phtml';