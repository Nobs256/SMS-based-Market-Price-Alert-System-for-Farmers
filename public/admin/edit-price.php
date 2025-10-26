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
$priceId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$priceEntry = null;

if (!$priceId || !($priceEntry = $priceService->getPriceById($priceId))) {
    $_SESSION['status'] = ['message' => 'Error: Invalid or non-existent price ID.', 'type' => 'error'];
    header('Location: dashboard.php?page=prices');
    exit;
}

// --- Handle Form Submission for Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $market = trim($_POST['market_name'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $priceDate = trim($_POST['price_date'] ?? '');

    if (!empty($market) && $price !== false && $price > 0 && !empty($priceDate)) {
        if ($priceService->updatePrice($priceId, $market, $price, $priceDate)) {
            $initialMessage = "Price for {$market} updated successfully.";

            // --- Trigger Automatic Broadcast ---
            $broadcastService = new BroadcastService();
            $broadcastResult = $broadcastService->executeBroadcast();

            // Combine the update message with the broadcast result message
            $combinedMessage = $initialMessage . " " . $broadcastResult['message'];
            $finalType = $broadcastResult['success'] ? 'success' : 'error';
            $_SESSION['status'] = ['message' => $combinedMessage, 'type' => $finalType];
            header('Location: dashboard.php?page=prices');
            exit;
        } else {
            $_SESSION['status'] = ['message' => 'Error: Could not update the price.', 'type' => 'error'];
        }
    } else {
        $_SESSION['status'] = ['message' => 'Error: All fields are required and price must be a valid number.', 'type' => 'error'];
    }
    // Redirect back to the edit page to show the error
    header('Location: edit-price.php?id=' . $priceId);
    exit;
}

// Include the view for displaying the form
require_once __DIR__ . '/../../templates/admin/edit-price.phtml';