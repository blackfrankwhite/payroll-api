<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\BenefitController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('company')->group(function () {
        Route::post('/', [CompanyController::class, 'register']);
        Route::get('/', [CompanyController::class, 'getCompany']);
        
        Route::prefix('employee')->group(function () {
            Route::post('/', [EmployeeController::class, 'addEmployee']);
            Route::get('/', [EmployeeController::class, 'getEmployees']);
            Route::put('{employeeID}', [EmployeeController::class, 'updateEmployee']);
            Route::delete('{employeeID}', [EmployeeController::class, 'deleteEmployee']);
            Route::get('{employeeID}', [EmployeeController::class, 'getEmployeeByID']);

            Route::prefix('{employeeID}/salaries')->group(function () {
                Route::post('/', [SalaryController::class, 'store']);
                Route::get('/', [SalaryController::class, 'index']);
                Route::get('{salaryID}', [SalaryController::class, 'show']);
                Route::put('{salaryID}', [SalaryController::class, 'update']);
                Route::delete('{salaryID}', [SalaryController::class, 'destroy']);
            });

            Route::prefix('{employeeID}/benefits')->group(function () {
                Route::post('/', [BenefitController::class, 'store']);
                Route::get('/', [BenefitController::class, 'index']);
                Route::get('{benefitID}', [BenefitController::class, 'show']);
                Route::put('{benefitID}', [BenefitController::class, 'update']);
                Route::delete('{benefitID}', [BenefitController::class, 'destroy']);
            });
        });
    });
});