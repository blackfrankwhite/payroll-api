<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\IncentiveBonusService;

class IncentiveBonusController extends Controller
{
    protected $incentiveBonusService;

    public function __construct(IncentiveBonusService $incentiveBonusService)
    {
        $this->incentiveBonusService = $incentiveBonusService;
    }

    public function index(Request $request, $employeeID)
    {
        $userId = $request->user()->id;
        $incentiveBonuses = $this->incentiveBonusService->getIncentiveBonuses($userId, $employeeID);
        return response()->json($incentiveBonuses);
    }

    public function store(Request $request, $employeeID)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'required|string',
            'percent' => 'required|numeric|min:0|max:100',
            'payment_currency' => 'required|string|size:3',
            'calculation_currency' => 'required|string|size:3',
            'includes_income_tax' => 'sometimes|boolean',
            'includes_employee_pension' => 'sometimes|boolean',
            'includes_company_pension' => 'sometimes|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'current_benefit' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $incentiveBonus = $this->incentiveBonusService->addIncentiveBonus($userId, $employeeID, $validator->validated());

        return response()->json($incentiveBonus, 201);
    }

    public function show(Request $request, $employeeID, $incentiveBonusID)
    {
        $userId = $request->user()->id;
        $incentiveBonus = $this->incentiveBonusService->getIncentiveBonus($userId, $employeeID, $incentiveBonusID);
        return response()->json($incentiveBonus);
    }

    public function update(Request $request, $employeeID, $incentiveBonusID)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'sometimes|string',
            'percent' => 'sometimes|numeric|min:0|max:100',
            'payment_currency' => 'sometimes|string|size:3',
            'calculation_currency' => 'sometimes|string|size:3',
            'includes_income_tax' => 'sometimes|boolean',
            'includes_employee_pension' => 'sometimes|boolean',
            'includes_company_pension' => 'sometimes|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'current_benefit' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $incentiveBonus = $this->incentiveBonusService->updateIncentiveBonus($userId, $employeeID, $incentiveBonusID, $validator->validated());

        return response()->json($incentiveBonus);
    }

    public function destroy(Request $request, $employeeID, $incentiveBonusID)
    {
        $userId = $request->user()->id;
        $this->incentiveBonusService->deleteIncentiveBonus($userId, $employeeID, $incentiveBonusID);
        return response()->json(['message' => 'Incentive bonus deleted successfully'], 200);
    }
}