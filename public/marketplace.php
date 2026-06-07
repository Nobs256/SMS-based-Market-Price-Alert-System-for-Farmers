<?php
require_once '../vendor/autoload.php';
require_once '../src/Database.php';
require_once '../src/ListingService.php';

use App\Database;
use App\ListingService;

$db = Database::getInstance();
$listingService = new ListingService($db);

// Get filter parameters from the search form
$variety = $_GET['variety'] ?? null;
$location = $_GET['location'] ?? null;

// Fetch all available potato listings
$listings = $listingService->getActiveListings($variety, $location);

$pageTitle = "Potato Marketplace - Rubanda District";

include '../templates/partials/header.phtml';
include '../templates/public/marketplace.phtml';
include '../templates/partials/footer.phtml';