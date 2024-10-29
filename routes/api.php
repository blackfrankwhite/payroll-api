<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    dd('test');
    
    return $request->user();
})->middleware('auth:sanctum');
