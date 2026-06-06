<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\FarmerService;
use App\PriceService;
use App\AuthService;
use App\BroadcastService;
use App\LogService;

// We'll use a session to store status messages
session_start();

// --- Authentication Check ---
$authService = new AuthService();
if (!$authService->isLoggedIn()) {
    // If not logged in, redirect to login page
    header('Location: login.php');
    exit;
}

$farmerService = new FarmerService();
$priceService = new PriceService();
$logService = new LogService();

// Determine which page to show
$page = $_GET['page'] ?? 'home';

// --- Handle Form Submissions ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check which form was submitted
    if (isset($_POST['add_farmer'])) {
        $names = trim($_POST['names'] ?? '');
        $phone = trim($_POST['phone_number'] ?? '');
        $lang = trim($_POST['language'] ?? 'en');

        if (!empty($names) && !empty($phone)) {
            if ($farmerService->addFarmer($names, $phone, $lang)) {
                $_SESSION['status'] = ['message' => "Farmer '{$names}' added successfully.", 'type' => 'success'];
            } else {
                $_SESSION['status'] = ['message' => 'Error: Could not add farmer. The phone number might already exist.', 'type' => 'error'];
            }
        } else {
            $_SESSION['status'] = ['message' => 'Error: Farmer name and phone number are required.', 'type' => 'error'];
        }
    } elseif (isset($_POST['add_price'])) {
        $market = trim($_POST['market_name'] ?? '');
        $priceKg = filter_input(INPUT_POST, 'price_per_kg', FILTER_VALIDATE_FLOAT);
        $priceSack = filter_input(INPUT_POST, 'price_per_sack', FILTER_VALIDATE_FLOAT);
        $date = date('Y-m-d');

        if (!empty($market) && $priceKg !== false && $priceSack !== false && $priceKg > 0) {
            if ($priceService->addPrice($market, $priceKg, $priceSack, $date)) {
                $initialMessage = "Price for {$market} added successfully.";

                // --- Trigger Automatic Broadcast ---
                $broadcastService = new BroadcastService();
                $broadcastResult = $broadcastService->executeBroadcast();

                // Combine the price addition message with the broadcast result message
                $combinedMessage = $initialMessage . " " . $broadcastResult['message'];
                $finalType = $broadcastResult['success'] ? 'success' : 'error';
                $_SESSION['status'] = ['message' => $combinedMessage, 'type' => $finalType];
            } else {
                $_SESSION['status'] = ['message' => 'Error: Could not add the price.', 'type' => 'error'];
            }
        } else {
            $_SESSION['status'] = ['message' => 'Error: Market name and a valid price are required.', 'type' => 'error'];
        }
    } elseif (isset($_POST['delete_farmer'])) {
        $farmerId = filter_input(INPUT_POST, 'farmer_id', FILTER_VALIDATE_INT)
                   ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
                   ?: filter_input(INPUT_GET, 'farmer_id', FILTER_VALIDATE_INT)
                   ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($farmerId && $farmerId > 0) {
            if ($farmerService->deleteFarmer($farmerId)) {
                $_SESSION['status'] = ['message' => 'Farmer deleted successfully.', 'type' => 'success'];
            } else {
                $_SESSION['status'] = ['message' => 'Error: Could not delete the farmer.', 'type' => 'error'];
            }
        } else {
            $_SESSION['status'] = ['message' => 'Error: Invalid farmer ID.', 'type' => 'error'];
        }
    } elseif (isset($_POST['delete_price'])) {
        $priceId = (int) ($_POST['price_id'] ?? $_GET['price_id'] ?? 0);
        if ($priceId && $priceId > 0) {
            if ($priceService->deletePrice($priceId)) {
                $_SESSION['status'] = ['message' => 'Price deleted successfully.', 'type' => 'success'];
            } else {
                $_SESSION['status'] = ['message' => 'Error: Could not delete the price.', 'type' => 'error'];
            }
        } else {
            $_SESSION['status'] = ['message' => 'Error: Invalid price ID.', 'type' => 'error'];
        }
    }
    // Redirect back to the relevant page to prevent form resubmission
    $redirectPage = 'home';
    if (isset($_POST['add_farmer']) || isset($_POST['delete_farmer'])) {
        $redirectPage = 'farmers';
    } elseif (isset($_POST['add_price']) || isset($_POST['delete_price'])) {
        $redirectPage = 'prices';
    }
    header('Location: dashboard.php?page=' . $redirectPage);
    exit;
}

// --- Fetch Data for Display ---
switch ($page) {
    case 'farmers':
        $totalFarmers = $farmerService->getFarmerCount();
        $perPage = 10; // Number of farmers to display per page
        $totalPages = ceil($totalFarmers / $perPage);
        $currentPage = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        if ($currentPage > $totalPages && $totalPages > 0) { $currentPage = $totalPages; }
        $paginatedFarmers = $farmerService->getFarmersPaginated($currentPage, $perPage);
        break;
    case 'logs':
        $recentLogs = $logService->getLogs(50); // Show more logs on this page
        break;
    case 'prices':
        $allPrices = $priceService->getAllPrices();
        break;
    case 'home':
        $farmerStatsByLang = $farmerService->getFarmerCountByLanguage();
        $farmerStatsJson = json_encode($farmerStatsByLang);
        $totalFarmers = $farmerService->getFarmerCount();
        break;
    case 'add-price':
    case 'add-farmer':
        // No data fetching needed for form pages
        break;
}

// Include the view
require_once __DIR__ . '/../../templates/admin/dashboard.phtml';