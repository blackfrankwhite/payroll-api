<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('company')->group(function () {
        Route::post('/', [CompanyController::class, 'register']);
        Route::get('/', [CompanyController::class, 'getCompany']);
        
        Route::prefix('employee')->group(function () {
            Route::post('/', [CompanyController::class, 'addEmployee']);
            Route::get('/', [CompanyController::class, 'getEmployees']);
            Route::put('{employeeID}', [CompanyController::class, 'updateEmployee']);
            Route::delete('{employeeID}', [CompanyController::class, 'deleteEmployee']);
            Route::get('{employeeID}', [CompanyController::class, 'getEmployeeByID']);
        });

        Route::prefix('users')->group(function () {
            Route::post('/', [CompanyController::class, 'inviteCompanyUser']);
            Route::get('/', [CompanyController::class, 'getCompanyUsers']);
            Route::put('{userID}', [CompanyController::class, 'updateCompanyUser']);
            Route::delete('{userID}', [CompanyController::class, 'deleteCompanyUser']);
            Route::get('{userID}', [CompanyController::class, 'getCompanyUserByID']);
        });
    });
});