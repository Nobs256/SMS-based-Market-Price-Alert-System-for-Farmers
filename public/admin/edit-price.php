<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\AuthService;
use App\PriceService;
use App\BroadcastService;

session_start();

// --- Authentication Check ---
$authService = new AuthService();
if (!$authService->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$priceService = new PriceService();
$priceId = (int) ($_GET['price_id'] ?? $_POST['price_id'] ?? 0);
$price = null;

if (!$priceId || !($price = $priceService->getPriceById($priceId))) {
    $_SESSION['status'] = ['message' => 'Error: Invalid or non-existent price ID.', 'type' => 'error'];
    header('Location: dashboard.php?page=prices');
    exit;
}

// --- Handle Form Submission for Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $market = trim($_POST['market_name'] ?? '');
    $priceKg = filter_input(INPUT_POST, 'price_per_kg', FILTER_VALIDATE_FLOAT);
    $priceSack = filter_input(INPUT_POST, 'price_per_sack', FILTER_VALIDATE_FLOAT);
    $date = $_POST['price_date'] ?? date('Y-m-d');

    if (!empty($market) && $priceKg !== false && $priceSack !== false && $priceKg > 0) {
        if ($priceService->updatePrice($priceId, $market, $priceKg, $priceSack, $date)) {
            $initialMessage = "Price for '{$market}' updated successfully.";

            // --- Trigger Automatic Broadcast ---
            $broadcastService = new BroadcastService();
            $broadcastResult = $broadcastService->executeBroadcast();

            // Combine the price update message with the broadcast result message
            $combinedMessage = $initialMessage . " " . $broadcastResult['message'];
            $finalType = $broadcastResult['success'] ? 'success' : 'error';
            $_SESSION['status'] = ['message' => $combinedMessage, 'type' => $finalType];
            
            header('Location: dashboard.php?page=prices');
            exit;
        } else {
            $_SESSION['status'] = ['message' => 'Error: Could not update the price.', 'type' => 'error'];
        }
    } else {
        $_SESSION['status'] = ['message' => 'Error: All fields are required and must be valid.', 'type' => 'error'];
    }
    // Redirect back to the edit page to show the error
    header('Location: edit-price.php?price_id=' . $priceId);
    exit;
}

// Include the view for displaying the form
require_once __DIR__ . '/../../templates/admin/edit-price.phtml';