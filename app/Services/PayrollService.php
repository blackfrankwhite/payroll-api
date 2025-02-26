<?php

namespace App\Services;

use App\Repositories\PayrollRepository;
use App\Traits\CalculationsTrait;

class PayrollService
{
    use CalculationsTrait;

    protected $payrollRepository;

    public function __construct(PayrollRepository $payrollRepository)
    {
        $this->payrollRepository = $payrollRepository;
    }

    public function calculatePayroll(
        array $employeeIDs, 
        string $startDate, 
        string $endDate, 
        array $prorateAdjustments = [], 
        array $regularAdjustments = []
    ) {
        $data = $this->payrollRepository->getPayrollData(
            $employeeIDs, $startDate, $endDate, $prorateAdjustments, $regularAdjustments
        );
        $results = [];

        // Process Salaries:
        foreach ($data['salaries'] as $record) {
            $empId = $record->employee_id;
            $breakdown = json_decode($record->salary_breakdown, true);
            if (!isset($results[$empId])) {
                // Initialize employee record with payroll flags, details, and empty arrays for adjustments.
                $results[$empId] = [
                    'employee_id'               => $empId,
                    'salary_gross'              => 0.0,
                    'sum_benefits_gross'        => 0.0,
                    'sum_deductions_gross'      => 0.0,
                    // Aggregated breakdown values (to be calculated later)
                    'sum_gross'                 => 0.0,
                    'sum_pension'               => 0.0,
                    'sum_income_tax'            => 0.0,
                    'sum_net'                   => 0.0,
                    // Payroll flags (assumed consistent for the employee)
                    'includes_income_tax'       => $record->includes_income_tax,
                    'includes_employee_pension' => $record->includes_employee_pension,
                    'pension'                   => $record->pension,
                    // Employee details
                    'position'                  => $record->position,
                    'name'                      => $record->name,
                    'email'                     => $record->email,
                    'phone'                     => $record->phone,
                    'id_number'                 => $record->id_number,
                    'surname'                   => $record->surname,
                    'gender'                    => $record->gender,
                    'birth_date'                => $record->birth_date,
                    'bank_account'              => $record->bank_account,
                    'residency'                 => $record->residency,
                    'address'                   => $record->address,
                    'start_date'                => $record->employee_start_date,
                    'end_date'                  => $record->employee_end_date,
                    // Separate containers for adjustments.
                    'deductions'                => [],
                    'benefits'                  => [],
                ];
            }

            // Accumulate the gross (base) from each salary breakdown.
            $results[$empId]['salary_gross'] += (float) $breakdown['base'];
        }

        // Process Adjustments:
        foreach ($data['adjustments'] as $record) {
            $empId = $record->employee_id;
            $breakdown = json_decode($record->breakdown, true);

            // Skip adjustments if the employee hasn't any salary record.
            if (!isset($results[$empId])) {
                continue;
            }

            // Round the gross value for this adjustment.
            $grossValue = round((float) $breakdown['base'], 2);
            // Normalize the adjustment name; this will serve as the grouping key.
            $nameKey = strtolower(trim($record->adjustment_name));

            if ($record->adjustment_type === 'benefit') {
                // Sum up benefits with the same name.
                if (isset($results[$empId]['benefits'][$nameKey])) {
                    $results[$empId]['benefits'][$nameKey]['gross'] += $grossValue;
                } else {
                    $results[$empId]['benefits'][$nameKey] = [
                        'name'  => $record->adjustment_name,
                        'gross' => $grossValue,
                    ];
                }
                $results[$empId]['sum_benefits_gross'] += $grossValue;
            } elseif ($record->adjustment_type === 'deduction') {
                // Sum up deductions with the same name.
                if (isset($results[$empId]['deductions'][$nameKey])) {
                    $results[$empId]['deductions'][$nameKey]['gross'] += $grossValue;
                } else {
                    $results[$empId]['deductions'][$nameKey] = [
                        'name'  => $record->adjustment_name,
                        'gross' => $grossValue,
                    ];
                }
                $results[$empId]['sum_deductions_gross'] += $grossValue;
            }
        }

        // Finalize each employee's record:
        foreach ($results as $empId => $record) {
            // Round intermediate sums.
            $results[$empId]['salary_gross'] = round($record['salary_gross'], 2);
            $results[$empId]['sum_benefits_gross'] = round($record['sum_benefits_gross'], 2);
            $results[$empId]['sum_deductions_gross'] = round($record['sum_deductions_gross'], 2);

            $sumGross = $results[$empId]['salary_gross'];

            // Calculate the aggregated breakdown using the trait's method.
            $aggregatedBreakdown = $this->calculateBreakdown(
                $sumGross,
                $record['includes_income_tax'],
                $record['includes_employee_pension'],
                $record['pension']
            );

            $results[$empId]['sum_gross']       = $sumGross;
            $results[$empId]['sum_pension']     = $aggregatedBreakdown['pension'];
            $results[$empId]['sum_income_tax']  = $aggregatedBreakdown['income_tax'];
            $results[$empId]['sum_net']         = $aggregatedBreakdown['net'];

            // Compute final net amount: salary gross + total benefits - total deductions.
            $results[$empId]['sum_amount'] = round(
                $results[$empId]['salary_gross'] +
                $results[$empId]['sum_benefits_gross'] -
                $results[$empId]['sum_deductions_gross'],
                2
            );

            // Convert the associative arrays for benefits and deductions into indexed arrays.
            $results[$empId]['benefits'] = array_values($results[$empId]['benefits']);
            $results[$empId]['deductions'] = array_values($results[$empId]['deductions']);
        }

        return array_values($results);
    }
}
