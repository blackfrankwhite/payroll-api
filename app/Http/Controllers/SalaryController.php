<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SalaryService;

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
        return response()->json(['salaries' => $salaries]);
    }

    public function store(Request $request, $employeeID)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'required|in:monthly,daily,hourly,annually',
            'amount' => 'required|numeric',
            'currency' => 'required|string|size:3',
            'payment_type' => 'required|in:net,gross',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $employeeID = $request->route('employeeID');
        $salary = $this->salaryService->addSalary($userId, $employeeID, $validator->validated());
        return response()->json(['salary' => $salary], 201);
    }

    public function show(Request $request, $employeeID, $salaryID)
    {
        $userId = $request->user()->id;
        $employeeID = $request->route('employeeID');
        $salaryID = $request->route('salaryID');
        $salary = $this->salaryService->getSalary($userId, $employeeID, $salaryID);
        return response()->json(['salary' => $salary]);
    }

    public function update(Request $request, $employeeID, $salaryID)
    {
        $employeeID = $request->route('employeeID');
        $salaryID = $request->route('salaryID');

        $validator = \Validator::make($request->all(), [
            'type' => 'sometimes|in:monthly,daily,hourly,annually',
            'amount' => 'sometimes|numeric',
            'currency' => 'sometimes|string|size:3',
            'payment_type' => 'sometimes|in:net,gross',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $salary = $this->salaryService->updateSalary($userId, $employeeID, $salaryID, $validator->validated());
        return response()->json(['salary' => $salary]);
    }

    public function destroy(Request $request, $employeeID, $salaryID)
    {
        $employeeID = $request->route('employeeID');
        $salaryID = $request->route('salaryID');
        $userId = $request->user()->id;
        $this->salaryService->deleteSalary($userId, $employeeID, $salaryID);
        return response()->json(['message' => 'Salary deleted successfully'], 200);
    }
}
