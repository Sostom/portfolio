<?php
// Admin entrypoint for Vercel PHP serverless deployment
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);

// Route admin pages based on path
if ($path === '/admin/index.php' || $path === '/admin/') {
    include __DIR__ . '/../../admin/index.php';
} elseif ($path === '/admin/login.php') {
    include __DIR__ . '/../../admin/login.php';
} elseif ($path === '/admin/logout.php') {
    include __DIR__ . '/../../admin/logout.php';
} elseif ($path === '/admin/upload.php') {
    include __DIR__ . '/../../admin/upload.php';
} elseif ($path === '/admin/profil.php') {
    include __DIR__ . '/../../admin/profil.php';
} elseif ($path === '/admin/delete.php') {
    include __DIR__ . '/../../admin/delete.php';
} else {
    http_response_code(404);
    echo 'Admin page not found';
}
