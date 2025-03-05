<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TaxExemptionService;

class TaxExemptionController extends Controller
{
    protected $service;

    public function __construct(TaxExemptionService $service)
    {
        $this->service = $service;
    }

    // GET /employee/{employeeID}/tax-exemptions
    public function index(Request $request, $employeeID)
    {
        // Retrieve tax exemptions for the given employee.
        return response()->json($this->service->getByEmployee($employeeID), 200);
    }

    // POST /employee/{employeeID}/tax-exemptions
    public function store(Request $request, $employeeID)
    {
        $validator = \Validator::make($request->all(), [
            'name'           => 'required|string',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date',
            'renewable'      => 'sometimes|boolean',
            'amount'         => 'nullable|numeric',
            'percent'        => 'nullable|numeric',
            'constant'       => 'sometimes|boolean',
            'balance_amount' => 'nullable|numeric',
            'balance_date'   => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        // Ensure the tax exemption is connected to the employee from the route.
        $data['employee_id'] = $employeeID;

        $exemption = $this->service->create($data);
        return response()->json($exemption, 201);
    }

    // GET /employee/{employeeID}/tax-exemptions/{id}
    public function show(Request $request, $employeeID, $id)
    {
        return response()->json($this->service->find($id), 200);
    }

    // PUT /employee/{employeeID}/tax-exemptions/{id}
    public function update(Request $request, $employeeID, $id)
    {
        $validator = \Validator::make($request->all(), [
            'name'           => 'sometimes|required|string',
            'start_date'     => 'sometimes|nullable|date',
            'end_date'       => 'sometimes|nullable|date',
            'renewable'      => 'sometimes|boolean',
            'amount'         => 'sometimes|nullable|numeric',
            'percent'        => 'sometimes|nullable|numeric',
            'constant'       => 'sometimes|boolean',
            'balance_amount' => 'sometimes|nullable|numeric',
            'balance_date'   => 'sometimes|nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $exemption = $this->service->update($id, $validator->validated());
        return response()->json($exemption, 200);
    }

    // DELETE /employee/{employeeID}/tax-exemptions/{id}
    public function destroy(Request $request, $employeeID, $id)
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Deleted successfully'], 200);
    }
}
