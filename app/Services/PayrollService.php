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

    /**
     * Calculate payroll including salaries, monthly and one-time adjustments,
     * and apply tax exemptions based on the given payment date.
     *
     * @param  array   $employeeIDs
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  string  $paymentDate            Date when the payroll is paid
     * @param  array   $prorateAdjustments     List of prorated adjustment IDs
     * @param  array   $regularAdjustments     List of non-prorated (monthly) adjustment IDs
     * @param  array   $oneTimeBenefitIDs      Optional list of one-time benefit IDs to filter
     * @param  array   $oneTimeDeductionIDs    Optional list of one-time deduction IDs to filter
     * @return array
     */
    public function calculatePayroll(
        array $employeeIDs, 
        string $startDate, 
        string $endDate, 
        string $paymentDate,
        array $prorateAdjustments = [], 
        array $regularAdjustments = [],
        array $oneTimeBenefitIDs = [],
        array $oneTimeDeductionIDs = []
    ) {
        $data = $this->payrollRepository->getPayrollData(
            $employeeIDs, 
            $startDate, 
            $endDate, 
            $prorateAdjustments, 
            $regularAdjustments,
            $oneTimeBenefitIDs,
            $oneTimeDeductionIDs,
            $paymentDate
        );
        
        $results = [];

        // Process Salaries:
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
                    'deductions'                => [],
                    'benefits'                  => [],
                ];
            }

            // Accumulate the gross (base) from each salary breakdown.
            $results[$empId]['salary_gross'] += (float) $breakdown['base'];
        }

        dd($results);

        // Process Adjustments:
        foreach ($data['adjustments'] as $record) {
            $empId = $record->employee_id;
            $breakdown = json_decode($record->breakdown, true);

            // Skip adjustments if the employee doesn't have a salary record.
            if (!isset($results[$empId])) {
                continue;
            }

            // Round the gross value for this adjustment.
            $grossValue = round((float) $breakdown['base'], 2);
            // Normalize the adjustment name; this will serve as the grouping key.
            $nameKey = strtolower(trim($record->adjustment_name));

            if ($record->adjustment_type === 'benefit') {
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

        // Finalize each employee's record and apply tax exemption.
        // Index tax exemptions by employee_id.
        $taxExemptions = collect($data['tax_exemptions'])->keyBy('employee_id');

        foreach ($results as $empId => $record) {
            $results[$empId]['salary_gross'] = round($record['salary_gross'], 2);
            $results[$empId]['sum_benefits_gross'] = round($record['sum_benefits_gross'], 2);
            $results[$empId]['sum_deductions_gross'] = round($record['sum_deductions_gross'], 2);

            $sumAfterAdjustments = round(
                $results[$empId]['salary_gross'] +
                $results[$empId]['sum_benefits_gross'] -
                $results[$empId]['sum_deductions_gross'],
                2
            );
            $aggregatedBreakdown = $this->calculateBreakdown(
                $sumAfterAdjustments,
                $record['includes_income_tax'],
                $record['includes_employee_pension'],
                $record['pension']
            );

            $results[$empId]['sum_gross']       = $sumAfterAdjustments;
            $results[$empId]['sum_pension']     = $aggregatedBreakdown['pension'];
            $results[$empId]['sum_income_tax']  = $aggregatedBreakdown['income_tax'];
            $results[$empId]['sum_net']         = $aggregatedBreakdown['net'];

            // Apply tax exemption if available.
            $taxExemption = $taxExemptions->get($empId);
            if ($taxExemption) {
                if ($taxExemption->constant) {
                    $results[$empId]['sum_net_after_exemption'] = round($results[$empId]['sum_net'] + $results[$empId]['sum_income_tax'], 2);
                    $results[$empId]['remaining_exemption_limit'] = null;
                } elseif ($taxExemption->percent) {
                    $results[$empId]['sum_net_after_exemption'] = round(
                        $results[$empId]['sum_net'] + ($taxExemption->percent / 100 * $results[$empId]['sum_income_tax']),
                        2
                    );
                    $results[$empId]['remaining_exemption_limit'] = null;
                } elseif ($taxExemption->amount) {
                    // Use balance_amount if available, otherwise use amount.
                    $limit = $taxExemption->balance_amount ? $taxExemption->balance_amount : $taxExemption->amount;
                    if ($limit >= $sumAfterAdjustments) {
                        $results[$empId]['sum_net_after_exemption'] = round($results[$empId]['sum_net'] + $results[$empId]['sum_income_tax'], 2);
                        $results[$empId]['remaining_exemption_limit'] = round($limit - $sumAfterAdjustments, 2);
                    } else {
                        // When limit is less than or equal to sum_gross:
                        $L = $limit;
                        $X = $sumAfterAdjustments - $L;
                        $exemptedBreakdown = $this->calculateBreakdown(
                            $L,
                            $record['includes_income_tax'],
                            $record['includes_employee_pension'],
                            $record['pension']
                        );
                        // For the exempted portion, add both net and income tax.
                        $exemptedNet = $exemptedBreakdown['net'] + $exemptedBreakdown['income_tax'];
                        $nonExemptedBreakdown = $this->calculateBreakdown(
                            $X,
                            $record['includes_income_tax'],
                            $record['includes_employee_pension'],
                            $record['pension']
                        );
                        $nonExemptedNet = $nonExemptedBreakdown['net'];
                        $results[$empId]['sum_net_after_exemption'] = round($exemptedNet + $nonExemptedNet, 2);
                        $results[$empId]['remaining_exemption_limit'] = 0;
                    }
                } else {
                    $results[$empId]['sum_net_after_exemption'] = $results[$empId]['sum_net'];
                    $results[$empId]['remaining_exemption_limit'] = null;
                }
            } else {
                $results[$empId]['sum_net_after_exemption'] = $results[$empId]['sum_net'];
                $results[$empId]['remaining_exemption_limit'] = null;
            }

            // Convert adjustments arrays from associative to indexed.
            $results[$empId]['benefits'] = array_values($results[$empId]['benefits']);
            $results[$empId]['deductions'] = array_values($results[$empId]['deductions']);
        }

        return array_values($results);
    }
}
