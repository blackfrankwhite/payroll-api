<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SalaryService;
use App\Http\Resources\SalaryResource;

class SalaryController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    public function index(Request $request, $employeeID)
    {
        $userId = $request->user()->id;
        $employeeID = $request->route('employeeID');
        $salaries = $this->salaryService->getSalaries($userId, $employeeID);
        return SalaryResource::collection($salaries);
    }

    public function store(Request $request, $employeeID)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'required|in:daily,monthly_fixed,monthly_shifts,hourly',
            'amount' => 'required|numeric',
            'payment_currency' => 'required|string|size:3',
            'calculation_currency' => 'required|string|size:3',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'includes_income_tax' => 'sometimes|boolean',
            'includes_employee_pension' => 'sometimes|boolean',
            'includes_company_pension' => 'sometimes|boolean',
            'daily_salary_calculation_base' => 'sometimes|nullable|string|in:WORKING_DAYS,CALENDAR_DAYS',
            'daily_working_hours' => 'sometimes|nullable|numeric|min:1|max:24',
            'non_working_days' => 'sometimes|array',
            'non_working_days.*' => 'string|in:PUBLIC_HOLIDAYS_UNDER_GEORGIAN_LAW,EVERY_MONDAY,EVERY_TUESDAY,CUSTOM_DATES,EVERY_WEDNESDAY,EVERY_THURSDAY,EVERY_FRIDAY,EVERY_SATURDAY,EVERY_SUNDAY',
            'non_working_custom_dates' => 'sometimes|array',
            'non_working_custom_dates.*' => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error', 
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user()->id;
        $employeeID = $request->route('employeeID');
        $result = $this->salaryService->addSalary($userId, $employeeID, $validator->validated());

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        return new SalaryResource($result['data']);
    }

    public function show(Request $request, $employeeID, $salaryID)
    {
        $userId = $request->user()->id;
        $employeeID = $request->route('employeeID');
        $salaryID = $request->route('salaryID');
        $salary = $this->salaryService->getSalary($userId, $employeeID, $salaryID);
        return new SalaryResource($salary);
    }

    public function update(Request $request, $employeeID, $salaryID)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'sometimes|in:daily,monthly_fixed,monthly_shifts,hourly',
            'amount' => 'sometimes|numeric',
            'payment_currency' => 'sometimes|string|size:3',
            'calculation_currency' => 'sometimes|string|size:3',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'includes_income_tax' => 'sometimes|boolean',
            'includes_employee_pension' => 'sometimes|boolean',
            'includes_company_pension' => 'sometimes|boolean',
            'daily_salary_calculation_base' => 'sometimes|string|in:WORKING_DAYS,CALENDAR_DAYS',
            'daily_working_hours' => 'sometimes|numeric|min:1|max:24',
            'non_working_days' => 'sometimes|array',
            'non_working_days.*' => 'string|in:PUBLIC_HOLIDAYS_UNDER_GEORGIAN_LAW,EVERY_MONDAY,EVERY_TUESDAY,CUSTOM_DATES,EVERY_WEDNESDAY,EVERY_THURSDAY,EVERY_FRIDAY,EVERY_SATURDAY,EVERY_SUNDAY',
            'non_working_custom_dates' => 'sometimes|array',
            'non_working_custom_dates.*' => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error', 
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user()->id;
        $result = $this->salaryService->updateSalary($userId, $employeeID, $salaryID, $validator->validated());

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        return new SalaryResource($result['data']);
    }

    public function destroy(Request $request, $employeeID, $salaryID)
    {
        $userId = $request->user()->id;
        $result = $this->salaryService->deleteSalary($userId, $employeeID, $salaryID);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        return response()->json(['message' => $result['data']], 200);
    }
}
