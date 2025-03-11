<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::post('/upload', [AIController::class, 'analyzeImage'])->name('upload');
Route::get('/form-picture', [AIController::class, 'showFormPicture']);
// Route::get('/form-picture', [AIController::class, 'showFormPicture']);
