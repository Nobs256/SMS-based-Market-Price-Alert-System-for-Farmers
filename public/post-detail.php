<?php
require_once '../vendor/autoload.php';
require_once '../src/Database.php';
require_once '../src/PostService.php';

use App\Database;
use App\PostService;

$db = Database::getInstance();
$postService = new PostService($db);

$postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$post = $postId ? $postService->getPostById($postId) : null;

if (!$post) {
    header('Location: news.php');
    exit;
}

$pageTitle = $post['title'];
include '../templates/partials/header.phtml';
include '../templates/public/post-detail.phtml';
include '../templates/partials/footer.phtml';