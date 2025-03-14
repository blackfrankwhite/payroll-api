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
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after_or_equal:start_date',
            'payment_date'           => 'required|date',
            'employee_ids'           => 'required|array',
            'prorate_adjustments'    => 'sometimes|nullable|array',
            'regular_adjustments'    => 'sometimes|nullable|array',
            'one_time_benefit_ids'   => 'sometimes|nullable|array',
            'one_time_deduction_ids' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error', 
                'errors'  => $validator->errors()
            ], 422);
        }

        $startDate             = $request->input('start_date');
        $endDate               = $request->input('end_date');
        $paymentDate           = $request->input('payment_date');
        $employeeIDs           = $request->input('employee_ids');
        $prorateAdjustments    = $request->input('prorate_adjustments') ?? [];
        $regularAdjustments    = $request->input('regular_adjustments') ?? [];
        $oneTimeBenefitIDs     = $request->input('one_time_benefit_ids') ?? [];
        $oneTimeDeductionIDs   = $request->input('one_time_deduction_ids') ?? [];

        $payrollData = $this->payrollService->calculatePayroll(
            $employeeIDs, 
            $startDate, 
            $endDate, 
            $paymentDate,
            $prorateAdjustments,
            $regularAdjustments,
            $oneTimeBenefitIDs,
            $oneTimeDeductionIDs
        );

        return response()->json($payrollData);
    }
}
