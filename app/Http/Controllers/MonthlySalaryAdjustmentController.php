<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MonthlySalaryAdjustmentService;

class MonthlySalaryAdjustmentController extends Controller
{
    protected $service;

    public function __construct(MonthlySalaryAdjustmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return response()->json($this->service->getAll($request->user()->id), 200);
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string',
            'type' => 'required|in:benefit,deduction',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        return response()->json($this->service->create($request->user()->id, $validator->validated()), 201);
    }

    public function show(Request $request, $id)
    {
        return response()->json($this->service->find($request->user()->id, $id), 200);
    }

    public function update(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'type' => 'sometimes|in:benefit,deduction',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        return response()->json($this->service->update($request->user()->id, $id, $validator->validated()), 200);
    }

    public function destroy(Request $request, $id)
    {
        $this->service->delete($request->user()->id, $id);
        return response()->json(['message' => 'Deleted successfully'], 200);
    }
}
