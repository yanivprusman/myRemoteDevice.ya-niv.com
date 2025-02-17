<?php
use Illuminate\Support\Facades\Route;
Route::get('/', function ($path = null) {
    return view('index');
});
Route::get('/pageForm', function ($path = null) {
    return view('pageForm');
});
Route::get('/client', function ($path = null) {
    return view('websocketClient');
});
Route::get('/testFlatBuffers', function ($path = null) {
    return view('testFlatBuffers');
});
// Route::get('/flatBuffers.min.js', function () {
//     return response()->file(
//         public_path('flatbuffers/flatBuffers.min.js'), 
//         ['Content-Type' => 'application/javascript']
//     );
// });