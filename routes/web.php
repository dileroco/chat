<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/assets/{asset}', function (string $asset) {
    $assetPath = public_path('react/assets/'.$asset);

    if (!File::isFile($assetPath)) {
        abort(404);
    }

    $contentType = match (strtolower(pathinfo($assetPath, PATHINFO_EXTENSION))) {
        'js' => 'application/javascript',
        'css' => 'text/css',
        'json' => 'application/json',
        default => 'application/octet-stream',
    };

    return Response::file($assetPath, ['Content-Type' => $contentType]);
})->where('asset', '[^/]+');

Route::get('/{any}', function () {
    $path = public_path('react/index.html');
    if (!File::exists($path)) {
        abort(404);
    }
    return File::get($path);
})->where('any', '.*');
