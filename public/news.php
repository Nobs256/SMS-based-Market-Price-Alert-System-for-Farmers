<?php
require_once '../vendor/autoload.php';
require_once '../src/Database.php';
require_once '../src/PostService.php';

use App\Database;
use App\PostService;

$db = Database::getInstance();
$postService = new PostService($db);

$category = $_GET['category'] ?? null;
$posts = $postService->getAllPosts($category);

$pageTitle = "Farming Info & News - Rubanda Potato Hub";
include '../templates/partials/header.phtml';
include '../templates/public/news.phtml';
include '../templates/partials/footer.phtml';