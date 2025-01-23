<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BenefitService;

class BenefitController extends Controller
{
    protected $benefitService;

    public function __construct(BenefitService $benefitService)
    {
        $this->benefitService = $benefitService;
    }

    public function index(Request $request, $employeeID)
    {
        $userId = $request->user()->id;
        $benefits = $this->benefitService->getBenefits($userId, $employeeID);
        return response()->json(['benefits' => $benefits]);
    }

    public function store(Request $request, $employeeID)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'payment_currency' => 'required|string|size:3',
            'calculation_currency' => 'required|string|size:3',
            'amount' => 'required|numeric',
            'includes_income_tax' => 'sometimes|boolean',
            'includes_employee_pension' => 'sometimes|boolean',
            'includes_company_pension' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $benefit = $this->benefitService->addBenefit($userId, $employeeID, $validator->validated());
        return response()->json(['benefit' => $benefit], 201);
    }

    public function show(Request $request, $employeeID, $benefitID)
    {
        $userId = $request->user()->id;
        $benefit = $this->benefitService->getBenefit($userId, $employeeID, $benefitID);
        return response()->json(['benefit' => $benefit]);
    }

    public function update(Request $request, $employeeID, $benefitID)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'payment_currency' => 'sometimes|string|size:3',
            'calculation_currency' => 'sometimes|string|size:3',
            'amount' => 'sometimes|numeric',
            'includes_income_tax' => 'sometimes|boolean',
            'includes_employee_pension' => 'sometimes|boolean',
            'includes_company_pension' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $benefit = $this->benefitService->updateBenefit($userId, $employeeID, $benefitID, $validator->validated());
        return response()->json(['benefit' => $benefit]);
    }

    public function destroy(Request $request, $employeeID, $benefitID)
    {
        $userId = $request->user()->id;
        $this->benefitService->deleteBenefit($userId, $employeeID, $benefitID);
        return response()->json(['message' => 'Benefit deleted successfully'], 200);
    }
}
