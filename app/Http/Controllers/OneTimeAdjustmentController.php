<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OneTimeAdjustmentService;

class OneTimeAdjustmentController extends Controller
{
    protected $service;

    public function __construct(OneTimeAdjustmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the one-time adjustments for a given employee.
     */
    public function index(Request $request, $employeeID)
    {
        $adjustments = $this->service->getAllByEmployee($employeeID);
        return response()->json($adjustments, 200);
    }

    /**
     * Store a newly created one-time adjustment.
     */
    public function store(Request $request, $employeeID)
    {
        $validator = \Validator::make($request->all(), [
            'type'                      => 'nullable|string',
            'amount'                    => 'required|numeric',
            'calculation_currency'      => 'required|string|size:3',
            'includes_income_tax'       => 'required|boolean',
            'includes_employee_pension' => 'required|boolean',
            'date'                      => 'required|date',
            'description'               => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['employee_id'] = $employeeID;

        $adjustment = $this->service->create($data);
        return response()->json($adjustment, 201);
    }

    /**
     * Display the specified one-time adjustment.
     */
    public function show(Request $request, $employeeID, $id)
    {
        $adjustment = $this->service->find($id);
        return response()->json($adjustment, 200);
    }

    /**
     * Update the specified one-time adjustment.
     */
    public function update(Request $request, $employeeID, $id)
    {
        $validator = \Validator::make($request->all(), [
            'type'                      => 'sometimes|nullable|string',
            'amount'                    => 'sometimes|numeric',
            'calculation_currency'      => 'sometimes|string|size:3',
            'includes_income_tax'       => 'sometimes|boolean',
            'includes_employee_pension' => 'sometimes|boolean',
            'date'                      => 'sometimes|date',
            'description'               => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $adjustment = $this->service->update($id, $data);
        return response()->json($adjustment, 200);
    }

    /**
     * Remove the specified one-time adjustment.
     */
    public function destroy(Request $request, $employeeID, $id)
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function byCompany(Request $request)
    {
        $adjustments = $this->service->getAllByCompany($request->user()->id);
        return response()->json($adjustments, 200);
    }
}
