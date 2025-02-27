<?php

namespace App\Services;

use App\Repositories\PayrollRepository;

class PayrollService
{
    protected $payrollRepository;

    public function __construct(PayrollRepository $payrollRepository)
    {
        $this->payrollRepository = $payrollRepository;
    }

    /**
     * Calculate payroll for the given employees over the specified period.
     * All calculations are performed in MySQL.
     *
     * @param array  $employeeIDs
     * @param string $startDate
     * @param string $endDate
     * @param array  $prorateAdjustments
     * @param array  $regularAdjustments
     * @param array  $oneTimeDeductionIDs Optional one‑time deduction IDs filter.
     * @return array
     */
    public function calculatePayroll(
        array $employeeIDs,
        string $startDate,
        string $endDate,
        array $prorateAdjustments = [],
        array $regularAdjustments = [],
        array $oneTimeDeductionIDs = []
    ) {
        return $this->payrollRepository->getPayrollData(
            $employeeIDs, 
            $startDate, 
            $endDate, 
            $prorateAdjustments, 
            $regularAdjustments,
            $oneTimeDeductionIDs
        );
    }
}
