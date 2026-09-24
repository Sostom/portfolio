<?php
// Vercel PHP serverless entrypoint: all site pages
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

$path = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($path, PHP_URL_PATH);

// Serve assets directly
if (preg_match('#^/assets/.*#', $path)) {
    $file = __DIR__ . '/../../' . ltrim($path, '/');
    if (file_exists($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mime = [
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ][$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000, immutable');
        readfile($file);
        exit;
    }
    header("HTTP/1.0 404 Not Found");
    exit('Not found: ' . htmlspecialchars($path));
}

// Route site pages
if (preg_match('#^/admin/login\.php#', $path)) {
    // Redirect to the actual file for admin pages handled separately
    header('Location: /admin/login.php');
    exit;
}

// For root and site pages: serve from root index.php
// We include it directly for serverless execution
require_once __DIR__ . '/../../index.php';
