<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\IncomeTaxExemptionService;

class IncomeTaxExemptionController extends Controller
{
    protected $incomeTaxExemptionService;

    public function __construct(IncomeTaxExemptionService $incomeTaxExemptionService)
    {
        $this->incomeTaxExemptionService = $incomeTaxExemptionService;
    }

    public function index(Request $request, $employeeID)
    {
        $userId = $request->user()->id;
        $incomeTaxExemptions = $this->incomeTaxExemptionService->getIncomeTaxExemptions($userId, $employeeID);
        return response()->json($incomeTaxExemptions);
    }

    public function store(Request $request, $employeeID)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'nullable|string',
            'limit_type' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $incomeTaxExemption = $this->incomeTaxExemptionService->addIncomeTaxExemption($userId, $employeeID, $validator->validated());

        return response()->json($incomeTaxExemption, 201);
    }

    public function show(Request $request, $employeeID, $incomeTaxExemptionID)
    {
        $userId = $request->user()->id;
        $incomeTaxExemption = $this->incomeTaxExemptionService->getIncomeTaxExemption($userId, $employeeID, $incomeTaxExemptionID);
        return response()->json($incomeTaxExemption);
    }

    public function update(Request $request, $employeeID, $incomeTaxExemptionID)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'sometimes|string',
            'limit_type' => 'sometimes|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $incomeTaxExemption = $this->incomeTaxExemptionService->updateIncomeTaxExemption($userId, $employeeID, $incomeTaxExemptionID, $validator->validated());

        return response()->json($incomeTaxExemption);
    }

    public function destroy(Request $request, $employeeID, $incomeTaxExemptionID)
    {
        $userId = $request->user()->id;
        $this->incomeTaxExemptionService->deleteIncomeTaxExemption($userId, $employeeID, $incomeTaxExemptionID);
        return response()->json(['message' => 'Income tax exemption deleted successfully'], 200);
    }
}