<?php
// Directly include the home page (no redirect)
$welcomePath = __DIR__ . '/resources/views/welcome.blade.php';

if (!file_exists($welcomePath)) {
    die('Welcome page not found.');
}

// Read the Blade template
$content = file_get_contents($welcomePath);

// Replace Blade asset() helper with actual asset paths
// asset() in Laravel points to public directory
$content = preg_replace_callback(
    "/\{\{\s*asset\(['\"]([^'\"]+)['\"]\)\s*\}\}/",
    function($matches) {
        // Remove leading slash if present, then add it back for web path
        $path = ltrim($matches[1], '/');
        return '/' . $path;
    },
    $content
);

// Output the processed content
echo $content;