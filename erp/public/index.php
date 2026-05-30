<?php
// erp/public/index.php

// 1. FORCE ERROR REPORTING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. LOAD CONFIGURATION FIRST (Crucial for Environment Awareness)
require_once __DIR__ . '/../config/config.php';

// 3. RECURSIVE AUTOLOADER (Automatically finds Controllers/Models no matter how deep the folders are)
spl_autoload_register(function ($class_name) {
    $base_dirs = [
        __DIR__ . '/../app/controllers/',
        __DIR__ . '/../app/models/'
    ];

    foreach ($base_dirs as $base_dir) {
        if (!is_dir($base_dir)) continue;
        
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            if ($file->getFilename() === $class_name . '.php') {
                require_once $file->getPathname();
                return;
            }
        }
    }
});

// 4. LOAD ROUTER
$router_path = __DIR__ . '/../routes/web.php';
if (!file_exists($router_path)) {
    die("<h1>Setup Error</h1><p>Cannot find router file at: $router_path</p>");
}
require_once $router_path;

// 5. DEPLOYMENT-READY URL PARSING
// We use the BASE_URL from config.php to perfectly extract the exact route.
$parsed_base = parse_url(BASE_URL, PHP_URL_PATH);
$base_path = rtrim($parsed_base ? $parsed_base : '', '/');
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Account for root .htaccess hiding the '/public' folder from the URL
$base_path_no_public = preg_replace('#/public$#', '', $base_path);

// Slice out the base environment path to get the pure route (e.g. '/admin')
if ($base_path !== '' && strpos($request_uri, $base_path) === 0) {
    $path = substr($request_uri, strlen($base_path));
} elseif ($base_path_no_public !== '' && strpos($request_uri, $base_path_no_public) === 0) {
    $path = substr($request_uri, strlen($base_path_no_public));
} else {
    $path = $request_uri;
}

// Clean the final path
$path = rtrim($path, '/');

// 🚨 THE FIX: Automatically strip .php so old legacy links still work perfectly!
$path = preg_replace('/\.php$/', '', $path);

if (empty($path)) {
    $path = '/';
}

// 6. TRIGGER ROUTE
if (function_exists('handleRoute')) {
    handleRoute($path);
} else {
    die("<h1>Router Error</h1><p>The function handleRoute() is missing from your routes/web.php file.</p>");
}
?>