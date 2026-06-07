<?php
require_once '../../vendor/autoload.php';
require_once '../../src/Database.php';
require_once '../../src/FarmerAuthService.php';
require_once '../../src/ListingService.php';

use App\Database;
use App\FarmerAuthService;
use App\ListingService;

$auth = new FarmerAuthService();
if (!$auth->isFarmerLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$listingService = new ListingService($db);
$myListings = $listingService->getFarmerListings($_SESSION['farmer_id']);

$pageTitle = "Farmer Dashboard - " . $_SESSION['farmer_names'];
include '../../templates/partials/header.phtml';
include '../../templates/farmer/dashboard.phtml';
include '../../templates/partials/footer.phtml';