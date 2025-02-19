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

    public function calculatePayroll(array $employeeIDs, string $startDate, string $endDate, bool $prorateAdjustments = true)
    {
        $data = $this->payrollRepository->getPayrollData($employeeIDs, $startDate, $endDate, $prorateAdjustments);
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
    
                    'sum_benefits_gross' => 0,
                    'sum_deductions_gross' => 0,
    
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
    
        foreach ($data['adjustments'] as $record) {
            $empId = $record->employee_id;
            $breakdown = json_decode($record->adjustment_breakdown, true);
            $adjustmentName = strtolower(str_replace(' ', '_', $record->adjustment_name)) . '_gross';
            if (!isset($results[$empId])) continue; 
    
            if ($record->adjustment_type === 'benefit') {
                $results[$empId]['sum_benefits_gross'] += (float) $breakdown['base'];
            } elseif ($record->adjustment_type === 'deduction') {
                $results[$empId]['sum_deductions_gross'] += (float) $breakdown['base'];
            }
    
            if (!isset($results[$empId][$adjustmentName])) {
                $results[$empId][$adjustmentName] = 0;
            }

            $results[$empId][$adjustmentName] += (float) $breakdown['base'];
        }
    
        return array_values($results);
    }    
}
