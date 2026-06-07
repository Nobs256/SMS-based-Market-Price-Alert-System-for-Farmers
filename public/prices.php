<?php
require_once '../vendor/autoload.php';
require_once '../src/Database.php';
require_once '../src/PriceService.php';

use App\Database;
use App\PriceService;

$db = Database::getInstance();
$priceService = new PriceService();

// Fetch the latest prices for the table
$allPrices = $priceService->getLatestPrices();
$pageTitle = "Current Potato Market Prices - Rubanda";

include '../templates/partials/header.phtml';
include '../templates/public/prices.phtml';
include '../templates/partials/footer.phtml';