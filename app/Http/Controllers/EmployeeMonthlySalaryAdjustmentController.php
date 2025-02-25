<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmployeeMonthlySalaryAdjustmentService;

class EmployeeMonthlySalaryAdjustmentController extends Controller
{
    protected $service;

    public function __construct(EmployeeMonthlySalaryAdjustmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request, $employeeId)
    {
        return response()->json($this->service->getAllForEmployee($request->user()->id, $employeeId), 200);
    }

    public function store(Request $request, $employeeId)
    {
        $validator = \Validator::make($request->all(), [
            'monthly_salary_adjustment_id' => 'required|exists:monthly_salary_adjustments,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'calculation_currency' => 'required|string|size:3',
            'amount' => 'required|numeric',
            'includes_income_tax' => 'sometimes|boolean',
            'includes_employee_pension' => 'sometimes|boolean',
            'includes_company_pension' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        return response()->json($this->service->create($request->user()->id, $employeeId, $validator->validated()), 201);
    }

    public function show(Request $request, $employeeId, $id)
    {
        return response()->json($this->service->find($request->user()->id, $employeeId, $id), 200);
    }

    public function update(Request $request, $employeeId, $id)
    {
        $validator = \Validator::make($request->all(), [
            'amount' => 'sometimes|numeric',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        return response()->json($this->service->update($request->user()->id, $employeeId, $id, $validator->validated()), 200);
    }

    public function destroy(Request $request, $employeeId, $id)
    {
        $this->service->delete($request->user()->id, $employeeId, $id);
        return response()->json(['message' => 'Deleted successfully'], 200);
    }
}
