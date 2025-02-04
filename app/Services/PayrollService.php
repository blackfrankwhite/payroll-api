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

    public function calculatePayroll(array $employeeIDs, string $startDate, string $endDate)
    {
        $payrollData = $this->payrollRepository->getPayrollData($employeeIDs, $startDate, $endDate);

        return $payrollData;
    }
}
