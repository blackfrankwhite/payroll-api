<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmployeeTimeBasedSalaryAdjustmentService;

class EmployeeTimeBasedSalaryAdjustmentController extends Controller
{
    protected $service;

    public function __construct(EmployeeTimeBasedSalaryAdjustmentService $service)
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
            'time_based_salary_adjustment_id' => 'required|exists:time_based_salary_adjustments,id',
            'amount'                         => 'required|integer',
            'dates'                            => 'required|array',
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
            'amount' => 'sometimes|integer',
            'dates'    => 'sometimes|array',
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
