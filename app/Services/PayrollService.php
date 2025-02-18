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
        $data = $this->payrollRepository->getPayrollData($employeeIDs, $startDate, $endDate);
        dd($data);
        $results = [];
    
        // Process Salaries:
        foreach ($data['salaries'] as $record) {
            $empId = $record->employee_id;
            $breakdown = json_decode($record->salary_breakdown, true);
            if (!isset($results[$empId])) {
                $results[$empId] = [
                    'employee_id' => $empId,
                    'salary_gross' => 0,
                    'salary_pension' => 0,
                    'salary_income_tax' => 0,
                    'salary_net' => 0,
    
                    // Sum of all benefits & deductions
                    'sum_benefits_gross' => 0,
                    'sum_deductions_gross' => 0,
    
                    // Employee Details
                    'position' => $record->position,
                    'name' => $record->name,
                    'email' => $record->email,
                    'phone' => $record->phone,
                    'id_number' => $record->id_number,
                    'surname' => $record->surname,
                    'gender' => $record->gender,
                    'birth_date' => $record->birth_date,
                    'bank_account' => $record->bank_account,
                    'residency' => $record->residency,
                    'address' => $record->address,
                    'start_date' => $record->employee_start_date,
                    'end_date' => $record->employee_end_date,
                    'pension' => $record->pension,
                ];
            }
            $results[$empId]['salary_gross'] += (float) $breakdown['base'];
            $results[$empId]['salary_pension'] += (float) $breakdown['pension'];
            $results[$empId]['salary_income_tax'] += (float) $breakdown['income_tax'];
            $results[$empId]['salary_net'] += (float) $breakdown['net'];
        }
    
        // Process Adjustments:
        foreach ($data['adjustments'] as $record) {
            $empId = $record->employee_id;
            $breakdown = json_decode($record->adjustment_breakdown, true);
            $adjustmentName = strtolower(str_replace(' ', '_', $record->adjustment_name)) . '_gross'; // Convert to valid key
    
            if (!isset($results[$empId])) {
                continue;
            }
    
            // Store each benefit/deduction separately
            if ($record->adjustment_type === 'benefit') {
                $results[$empId]['sum_benefits_gross'] += (float) $breakdown['base'];
            } elseif ($record->adjustment_type === 'deduction') {
                $results[$empId]['sum_deductions_gross'] += (float) $breakdown['base'];
            }
    
            // Add individual benefit/deduction breakdown dynamically
            if (!isset($results[$empId][$adjustmentName])) {
                $results[$empId][$adjustmentName] = 0;
            }
            $results[$empId][$adjustmentName] += (float) $breakdown['base'];
        }
    
        return array_values($results);
    }    
}
