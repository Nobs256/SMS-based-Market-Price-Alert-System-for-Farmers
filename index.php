<?php
require_once 'vendor/autoload.php';
require_once 'src/Database.php';
require_once 'src/PriceService.php';
require_once 'src/PostService.php';
require_once 'src/ListingService.php';
require_once 'src/FarmerService.php';

use App\Database;
use App\PriceService;
use App\PostService;
use App\ListingService;

$db = Database::getInstance();
$priceService = new PriceService();
$postService = new PostService($db);
$listingService = new ListingService($db);

// Fetch data for the landing page
$latestPrices = $priceService->getLatestPrices();
$recentNews = array_slice($postService->getAllPosts(), 0, 4); // Fetch latest 4 items (News, Guidelines, or Blogs)
$featuredListings = array_slice($listingService->getActiveListings(), 0, 4);

$pageTitle = "Welcome to Rubanda Potato Hub";
// Professional UI Header
include 'templates/partials/header.phtml'; 

// Main Landing Page View
include 'templates/public/home.phtml';

include 'templates/partials/footer.phtml';
