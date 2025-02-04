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
     * Calculate payroll for given employees and period.
     *
     * Returns an array of records keyed by employee_id with the following fields:
     * - salary_gross, salary_pension, salary_income_tax, salary_net
     * - benefit_gross, benefit_pension, benefit_income_tax, benefit_net
     * - deduction_gross, deduction_pension, deduction_income_tax, deduction_net
     *
     * @param array  $employeeIDs
     * @param string $startDate   Format: YYYY-MM-DD
     * @param string $endDate     Format: YYYY-MM-DD
     * @return array
     */
    public function calculatePayroll(array $employeeIDs, string $startDate, string $endDate)
    {
        // Retrieve payroll data from the repository.
        // The repository returns an associative array with keys: 'salaries', 'benefits', 'deductions'.
        $data = $this->payrollRepository->getPayrollData($employeeIDs, $startDate, $endDate);

        // Initialize a final result array keyed by employee_id.
        $final = [];

        // Process salaries:
        // Each salary record contains a JSON column named "salary_breakdown".
        foreach ($data['salaries'] as $record) {
            $employeeId = $record->employee_id;
            // Decode the JSON breakdown.
            $breakdown = json_decode($record->salary_breakdown, true);
            if (!isset($final[$employeeId])) {
                $final[$employeeId] = [
                    'employee_id' => $employeeId,
                    'salary_gross' => 0,
                    'salary_pension' => 0,
                    'salary_income_tax' => 0,
                    'salary_net' => 0,
                    'benefit_gross' => 0,
                    'benefit_pension' => 0,
                    'benefit_income_tax' => 0,
                    'benefit_net' => 0,
                    'deduction_gross' => 0,
                    'deduction_pension' => 0,
                    'deduction_income_tax' => 0,
                    'deduction_net' => 0,
                ];
            }
            // The JSON breakdown contains keys: base, pension, income_tax, net.
            $final[$employeeId]['salary_gross'] += (float) $breakdown['base'];
            $final[$employeeId]['salary_pension'] += (float) $breakdown['pension'];
            $final[$employeeId]['salary_income_tax'] += (float) $breakdown['income_tax'];
            $final[$employeeId]['salary_net'] += (float) $breakdown['net'];
        }

        // Process benefits:
        // Each benefit record contains a JSON column named "benefit_breakdown".
        foreach ($data['benefits'] as $record) {
            $employeeId = $record->employee_id;
            $breakdown = json_decode($record->benefit_breakdown, true);
            if (!isset($final[$employeeId])) {
                $final[$employeeId] = [
                    'employee_id' => $employeeId,
                    'salary_gross' => 0,
                    'salary_pension' => 0,
                    'salary_income_tax' => 0,
                    'salary_net' => 0,
                    'benefit_gross' => 0,
                    'benefit_pension' => 0,
                    'benefit_income_tax' => 0,
                    'benefit_net' => 0,
                    'deduction_gross' => 0,
                    'deduction_pension' => 0,
                    'deduction_income_tax' => 0,
                    'deduction_net' => 0,
                ];
            }
            $final[$employeeId]['benefit_gross'] += (float) $breakdown['base'];
            $final[$employeeId]['benefit_pension'] += (float) $breakdown['pension'];
            $final[$employeeId]['benefit_income_tax'] += (float) $breakdown['income_tax'];
            $final[$employeeId]['benefit_net'] += (float) $breakdown['net'];
        }

        // Process deductions:
        // Each deduction record contains a JSON column named "deduction_breakdown".
        foreach ($data['deductions'] as $record) {
            $employeeId = $record->employee_id;
            $breakdown = json_decode($record->deduction_breakdown, true);
            if (!isset($final[$employeeId])) {
                $final[$employeeId] = [
                    'employee_id' => $employeeId,
                    'salary_gross' => 0,
                    'salary_pension' => 0,
                    'salary_income_tax' => 0,
                    'salary_net' => 0,
                    'benefit_gross' => 0,
                    'benefit_pension' => 0,
                    'benefit_income_tax' => 0,
                    'benefit_net' => 0,
                    'deduction_gross' => 0,
                    'deduction_pension' => 0,
                    'deduction_income_tax' => 0,
                    'deduction_net' => 0,
                ];
            }
            $final[$employeeId]['deduction_gross'] += (float) $breakdown['base'];
            $final[$employeeId]['deduction_pension'] += (float) $breakdown['pension'];
            $final[$employeeId]['deduction_income_tax'] += (float) $breakdown['income_tax'];
            $final[$employeeId]['deduction_net'] += (float) $breakdown['net'];
        }

        // Convert the final array to a zero-indexed array of objects.
        $result = array_values($final);

        return $result;
    }
}
