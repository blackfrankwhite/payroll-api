<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ValidateEmployeeOwnership;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\MonthlySalaryAdjustmentController;
use App\Http\Controllers\EmployeeMonthlySalaryAdjustmentController;
use App\Http\Controllers\OneTimeAdjustmentController;
use App\Http\Controllers\TaxExemptionController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('company')->group(function () {
        Route::post('/', [CompanyController::class, 'register']);
        Route::get('/', [CompanyController::class, 'getCompany']);

        Route::prefix('payroll')->group(function () {
            Route::get('/calculate', [PayrollController::class, 'calculatePayroll']);
        });

        Route::apiResource('monthly-salary-adjustments', MonthlySalaryAdjustmentController::class);

        Route::prefix('employee')->middleware(ValidateEmployeeOwnership::class)->group(function () {
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
    
            Route::apiResource('{employeeID}/monthly-salary-adjustments', EmployeeMonthlySalaryAdjustmentController::class);
            Route::apiResource('{employeeID}/one-time-adjustments', OneTimeAdjustmentController::class);
            Route::apiResource('{employeeID}/tax-exemptions', TaxExemptionController::class);
        });

        Route::get('one-time-adjustments', [OneTimeAdjustmentController::class, 'byCompany']);
    });
});
