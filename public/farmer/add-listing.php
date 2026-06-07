<?php
require_once '../../vendor/autoload.php';
require_once '../../src/Database.php';
require_once '../../src/FarmerAuthService.php';
require_once '../../src/ListingService.php';

use App\Database;
use App\FarmerAuthService;
use App\ListingService;

session_start();
$auth = new FarmerAuthService();

if (!$auth->isFarmerLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$listingService = new ListingService($db);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variety = trim($_POST['variety'] ?? '');
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_FLOAT);
    $unit = trim($_POST['unit'] ?? 'Sack');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $location = trim($_POST['location'] ?? '');
    $farmerId = $_SESSION['farmer_id'];

    // Handle Image Upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/listings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
            $imagePath = '/assets/uploads/listings/' . $fileName;
        }
    }

    if ($variety && $quantity && $price && $location) {
        if ($listingService->createListing($farmerId, $variety, $quantity, $unit, $price, $location, $imagePath)) {
            $_SESSION['status'] = ['message' => 'Stock listed successfully!', 'type' => 'success'];
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Failed to save listing.';
    } else {
        $error = 'Please fill in all required fields correctly.';
    }
}

$pageTitle = "Add Stock - Farmer Portal";
include '../../templates/partials/header.phtml';
include '../../templates/farmer/add-listing.phtml';
include '../../templates/partials/footer.phtml';