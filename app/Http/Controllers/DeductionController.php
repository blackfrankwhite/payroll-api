<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DeductionService;

class DeductionController extends Controller
{
    protected $deductionService;

    public function __construct(DeductionService $deductionService)
    {
        $this->deductionService = $deductionService;
    }

    public function index(Request $request, $employeeID)
    {
        $userId = $request->user()->id;
        $deductions = $this->deductionService->getDeductions($userId, $employeeID);
        return response()->json(['deductions' => $deductions]);
    }

    public function store(Request $request, $employeeID)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'required|string',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'payment_type' => 'required|in:net,gross',
            'currency' => 'required|string|size:3',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $deduction = $this->deductionService->addDeduction($userId, $employeeID, $validator->validated());
        return response()->json(['deduction' => $deduction], 201);
    }

    public function show(Request $request, $employeeID, $deductionID)
    {
        $userId = $request->user()->id;
        $deduction = $this->deductionService->getDeduction($userId, $employeeID, $deductionID);
        return response()->json(['deduction' => $deduction]);
    }

    public function update(Request $request, $employeeID, $deductionID)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'sometimes|string',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'payment_type' => 'sometimes|in:net,gross',
            'currency' => 'sometimes|string|size:3',
            'amount' => 'sometimes|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $deduction = $this->deductionService->updateDeduction($userId, $employeeID, $deductionID, $validator->validated());
        return response()->json(['deduction' => $deduction]);
    }

    public function destroy(Request $request, $employeeID, $deductionID)
    {
        $userId = $request->user()->id;
        $this->deductionService->deleteDeduction($userId, $employeeID, $deductionID);
        return response()->json(['message' => 'Deduction deleted successfully'], 200);
    }
}
