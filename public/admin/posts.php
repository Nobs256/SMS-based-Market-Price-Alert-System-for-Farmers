<?php
require_once '../../vendor/autoload.php';
require_once '../../src/Database.php';
require_once '../../src/AuthService.php';
require_once '../../src/PostService.php';

use App\Database;
use App\AuthService;
use App\PostService;

session_start();
$auth = new AuthService();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$postService = new PostService($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $adminId = $_SESSION['user_id'];

    if ($postService->addPost($title, $content, $category, $adminId)) {
        $_SESSION['status'] = ['message' => 'Post published successfully!', 'type' => 'success'];
    }
}

$posts = $postService->getAllPosts();
require_once '../../templates/admin/posts.phtml';