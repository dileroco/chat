<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/{any}', function () {
    $path = public_path('react/index.html');
    if (!File::exists($path)) {
        abort(404);
    }
    return File::get($path);
})->where('any', '.*');