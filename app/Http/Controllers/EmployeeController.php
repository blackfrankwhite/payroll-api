<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function addEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees',
            'phone' => 'sometimes|nullable|string|max:255',
            'position' => 'sometimes|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        return response()->json($this->employeeService->addEmployee($userId, $validator->validated()), 201);
    }

    public function getEmployees(Request $request)
    {
        $userId = $request->user()->id;
        return response()->json($this->employeeService->getEmployees($userId));
    }

    public function updateEmployee(Request $request, $employeeID)
    {
        $employeeID = $request->route('employeeID');

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|nullable|string|max:255',
            'position' => 'sometimes|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        return response()->json($this->employeeService->updateEmployee($userId, $employeeID, $validator->validated()));
    }

    public function deleteEmployee(Request $request, $employeeID)
    {
        $employeeID = $request->route('employeeID');
        $userId = $request->user()->id;
        $this->employeeService->deleteEmployee($userId, $employeeID);

        return response()->json(['message' => 'Employee deleted successfully'], 200);
    }

    public function getEmployeeByID(Request $request, $employeeID)
    {
        $employeeID = $request->route('employeeID');
        $userId = $request->user()->id;
        return response()->json($this->employeeService->getEmployeeByID($userId, $employeeID));
    }
}
