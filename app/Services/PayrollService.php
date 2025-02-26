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

    public function calculatePayroll(array $employeeIDs, string $startDate, string $endDate, array $prorateAdjustments = [], array $regularAdjustments = [])
    {
        $data = $this->payrollRepository->getPayrollData($employeeIDs, $startDate, $endDate, $prorateAdjustments, $regularAdjustments);
        $results = [];

         foreach ($data['salaries'] as $record) {
            $empId = $record->employee_id;
            $breakdown = json_decode($record->salary_breakdown, true);
            if (!isset($results[$empId])) {
                $results[$empId] = [
                    'employee_id'               => $empId,
                    'salary_gross'              => 0.0,
                    'sum_benefits_gross'        => 0.0,
                    'sum_deductions_gross'      => 0.0,
                    'sum_gross'                 => 0.0,
                    'sum_pension'               => 0.0,
                    'sum_income_tax'            => 0.0,
                    'sum_net'                   => 0.0,
                    'includes_income_tax'       => $record->includes_income_tax,
                    'includes_employee_pension' => $record->includes_employee_pension,
                    'pension'                   => $record->pension,
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
                ];
            }

            $results[$empId]['salary_gross'] += (float) $breakdown['base'];
        }

        foreach ($data['adjustments'] as $record) {
            $empId = $record->employee_id;
            $breakdown = json_decode($record->breakdown, true);
            $adjustmentName = strtolower(str_replace(' ', '_', $record->adjustment_name)) . '_gross';

            if (!isset($results[$empId])) {
                continue;
            }

            if ($record->adjustment_type === 'benefit') {
                $results[$empId]['sum_benefits_gross'] += (float) $breakdown['base'];
            } elseif ($record->adjustment_type === 'deduction') {
                $results[$empId]['sum_deductions_gross'] += (float) $breakdown['base'];
            }

            if (!isset($results[$empId][$adjustmentName])) {
                $results[$empId][$adjustmentName] = 0.0;
            }

            $results[$empId][$adjustmentName] += (float) $breakdown['base'];
        }

        foreach ($results as $empId => $record) {
            $results[$empId]['salary_gross'] = round($record['salary_gross'], 2);
            $results[$empId]['sum_benefits_gross'] = round($record['sum_benefits_gross'], 2);
            $results[$empId]['sum_deductions_gross'] = round($record['sum_deductions_gross'], 2);

            $sumGross = $results[$empId]['salary_gross'];

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

            $results[$empId]['sum_amount'] = round(
                $results[$empId]['salary_gross'] +
                $results[$empId]['sum_benefits_gross'] -
                $results[$empId]['sum_deductions_gross'],
                2
            );
        }

        return array_values($results);
    }
}
