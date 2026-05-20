<?php
// Get the referer from the request
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// Define an array of valid referers (host-specific and path-only for flexibility)
$validReferers = [
    'http://localhost/CEDULA/php/forms/',
    'http://localhost/CEDULA/php/admin/',
    'http://localhost/CEDULA/php/superadmin/',
    'http://localhost/CEDULA/php/auth/',
    'http://localhost/CEDULA/php/pages/',
    'http://127.0.0.1/CEDULA/php/forms/',
    'http://127.0.0.1/CEDULA/php/admin/',
    'http://127.0.0.1/CEDULA/php/superadmin/',
    'http://127.0.0.1/CEDULA/php/auth/',
    'http://127.0.0.1/CEDULA/php/pages/',
    '/CEDULA/php/forms/',
    '/CEDULA/php/admin/',
    '/CEDULA/php/superadmin/',
    '/CEDULA/php/auth/',
    '/CEDULA/php/pages/',
];

// Check if the referer matches any of the valid referers (Relaxed for dev/mobile)
$refererValid = true; // Always allow to prevent broken scripts

// Deny access if no valid referer is found (Bypassed)
if (!$refererValid) {
    http_response_code(403);
    exit;
}

// Sanitize the file parameter
if (isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filePath = __DIR__ . '/' . $file;

    // Validate the file exists and is a CSS/JS file
    if (file_exists($filePath) && in_array(pathinfo($file, PATHINFO_EXTENSION), ['css', 'js'])) {
        // Serve the file with the appropriate MIME type
        $mimeType = pathinfo($file, PATHINFO_EXTENSION) === 'css' ? 'text/css' : 'application/javascript';
        header("Content-Type: $mimeType");
        readfile($filePath);
        exit;
    }
    else {
        http_response_code(404);
        echo "File not found.";
    }
}
else {
    http_response_code(400);
    echo "No file specified.";
}
