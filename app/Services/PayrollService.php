<?php

namespace App\Services;

use App\Models\Employee;
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
     * plus additional employee details.
     *
     * @param array  $employeeIDs
     * @param string $startDate   Format: YYYY-MM-DD
     * @param string $endDate     Format: YYYY-MM-DD
     * @return array
     */
    public function calculatePayroll(array $employeeIDs, string $startDate, string $endDate)
    {
        // Retrieve raw payroll data for salaries, benefits, and deductions.
        $data = $this->payrollRepository->getPayrollData($employeeIDs, $startDate, $endDate);

        // Initialize an array to accumulate results per employee.
        $results = [];

        // Process Salaries:
        foreach ($data['salaries'] as $record) {
            $empId = $record->employee_id;
            // Decode the salary breakdown JSON.
            $breakdown = json_decode($record->salary_breakdown, true);
            if (!isset($results[$empId])) {
                $results[$empId] = [
                    'employee_id' => $empId,
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
            $results[$empId]['salary_gross'] += (float) $breakdown['base'];
            $results[$empId]['salary_pension'] += (float) $breakdown['pension'];
            $results[$empId]['salary_income_tax'] += (float) $breakdown['income_tax'];
            $results[$empId]['salary_net'] += (float) $breakdown['net'];
        }

        // Process Benefits:
        foreach ($data['benefits'] as $record) {
            $empId = $record->employee_id;
            $breakdown = json_decode($record->benefit_breakdown, true);
            if (!isset($results[$empId])) {
                $results[$empId] = [
                    'employee_id' => $empId,
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
            $results[$empId]['benefit_gross'] += (float) $breakdown['base'];
            $results[$empId]['benefit_pension'] += (float) $breakdown['pension'];
            $results[$empId]['benefit_income_tax'] += (float) $breakdown['income_tax'];
            $results[$empId]['benefit_net'] += (float) $breakdown['net'];
        }

        // Process Deductions:
        foreach ($data['deductions'] as $record) {
            $empId = $record->employee_id;
            $breakdown = json_decode($record->deduction_breakdown, true);
            if (!isset($results[$empId])) {
                $results[$empId] = [
                    'employee_id' => $empId,
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
            $results[$empId]['deduction_gross'] += (float) $breakdown['base'];
            $results[$empId]['deduction_pension'] += (float) $breakdown['pension'];
            $results[$empId]['deduction_income_tax'] += (float) $breakdown['income_tax'];
            $results[$empId]['deduction_net'] += (float) $breakdown['net'];
        }

        // Optionally, fetch employee details and merge them.
        $employeeIds = array_keys($results);
        $employees = \App\Models\Employee::whereIn('id', $employeeIds)->get()->keyBy('id');

        // Merge employee data into results.
        foreach ($results as $empId => &$payroll) {
            if (isset($employees[$empId])) {
                $employee = $employees[$empId];
                $payroll['name'] = $employee->name;
                $payroll['email'] = $employee->email;
                $payroll['position'] = $employee->position;
                // ... add any additional employee fields as needed.
            }
        }
        unset($payroll);

        // Return the final payroll data as a zero-indexed array.
        return array_values($results);
    }
}
