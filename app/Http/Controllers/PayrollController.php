<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PayrollService;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function calculatePayroll(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'employee_ids' => 'required|array',
            'prorate_ajdustments' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $employeeIDs = $request->employee_ids;
        $payrollData = $this->payrollService->calculatePayroll($employeeIDs, $startDate, $endDate, $request->prorate_adjustments ?? true);
        return response()->json($payrollData);
    }
}
