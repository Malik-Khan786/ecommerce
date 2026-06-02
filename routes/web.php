<?php

use Illuminate\Support\Facades\Route;

Route::get('/offline', function () {
    return response('<html><body style="font-family:sans-serif;text-align:center;padding:80px"><h1>📶 You\'re Offline</h1><p>Please check your internet connection and try again.</p></body></html>', 200, ['Content-Type' => 'text/html']);
});

Route::get('/{any}', function () {
    $manifest = json_decode(file_get_contents(public_path('spa/.vite/manifest.json')), true);
    $entry    = $manifest['resources/js/main.js'];
    $base     = '/spa/';
    $js       = $base . $entry['file'];
    $css      = $base . ($entry['css'][0] ?? '');

    return response('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#f97316" />
    <title>Huzaifa Mobile - Pakistan\'s Best Mobile Store</title>
    <link rel="icon" href="/favicon.ico" />
    <link rel="manifest" href="/manifest.json" />
    <link rel="apple-touch-icon" href="/icon-192.png" />
    <link rel="stylesheet" href="' . $css . '" />
</head>
<body>
    <div id="app"></div>
    <script type="module" src="' . $js . '"></script>
</body>
</html>', 200, ['Content-Type' => 'text/html']);
})->where('any', '.*');
