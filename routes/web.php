<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Proxy route for S3 images to avoid CORS issues
Route::get('storage/s3/{path}', function ($path) {
    $s3Path = urldecode($path);
    $disk = Storage::disk('s3');

    if (!$disk->exists($s3Path)) {
        abort(404);
    }

    $file = $disk->get($s3Path);
    $extension = pathinfo($s3Path, PATHINFO_EXTENSION);
    $mimeType = match($extension) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        default => 'application/octet-stream',
    };

    return response($file, 200, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*');