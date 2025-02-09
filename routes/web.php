<?php

use Illuminate\Support\Facades\Route;

// Route::get('/{path?}', function ($path = null) {
//     return view('index');
// })->where('path', '.*');
Route::get('/', function ($path = null) {
    return view('index');
});

Route::get('/pageForm', function ($path = null) {
    return view('pageForm');
});

Route::get('/client', function ($path = null) {
    return view('websocketClient');
});

