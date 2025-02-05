<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class PayrollRepository
{
    public function getPayrollData(array $employeeIDs, $startDate, $endDate)
    {
        // 1. Salaries calculation
        $salaries = DB::table('salaries')
            ->join('employees', 'salaries.employee_id', '=', 'employees.id')
            ->whereNull('employees.deleted_at') 
            ->selectRaw("
                salaries.id AS salary_id,
                salaries.employee_id,
                salaries.amount AS salary_amount,
                salaries.start_date AS salary_start,
                salaries.end_date AS salary_end,
                salaries.daily_salary_calculation_base,
                salaries.type,
                salaries.payment_currency,
                salaries.includes_income_tax,
                salaries.includes_employee_pension,
                
                -- Determine the effective period for this salary record.
                GREATEST(salaries.start_date, ?) AS period_start,
                LEAST(COALESCE(salaries.end_date, ?), ?) AS period_end,
                
                -- Compute the prorated salary and get the full breakdown as JSON.
                calculate_salary_breakdown(
                    CAST(
                        calculate_prorated_salary_for_period(
                            salaries.amount,
                            GREATEST(salaries.start_date, ?),
                            LEAST(COALESCE(salaries.end_date, ?), ?),
                            salaries.id,
                            salaries.daily_salary_calculation_base
                        ) AS DECIMAL(18,2)
                    ),
                    salaries.includes_income_tax,
                    salaries.includes_employee_pension
                ) AS salary_breakdown
            ", [
                // Bound parameters for salaries period boundaries:
                $startDate, $endDate, $endDate,
                // For calculate_prorated_salary_for_period parameters:
                $startDate, $endDate, $endDate,
            ])
            ->whereIn('salaries.employee_id', $employeeIDs)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('salaries.start_date', '<=', $endDate)
                      ->where(function ($subQuery) use ($startDate) {
                          $subQuery->where('salaries.end_date', '>=', $startDate)
                                   ->orWhereNull('salaries.end_date');
                      });
            })
            ->groupBy('salaries.id')
            ->orderBy('salaries.employee_id')
            ->get();

        // 2. Benefits calculation
        // Benefits are not linked by salary_id. We connect them to the period via start_date/end_date.
        // We assume a function calculate_prorated_benefit_for_period exists that has the same signature
        // as calculate_prorated_salary_for_period except that salary_id is not needed (we pass 0).
        // Also, we assume that for benefits, tax flags are not applicable so we pass 0 for both.
        $benefits = DB::table('benefits')
            ->join('employees', 'salaries.employee_id', '=', 'employees.id')
            ->whereNull('employees.deleted_at') 
            ->selectRaw("
                benefits.id AS benefit_id,
                benefits.employee_id,
                benefits.amount AS benefit_amount,
                benefits.start_date AS benefit_start,
                benefits.end_date AS benefit_end,
                -- Determine the effective period for the benefit.
                GREATEST(benefits.start_date, ?) AS period_start,
                LEAST(COALESCE(benefits.end_date, ?), ?) AS period_end,
                CAST(
                    calculate_prorated_benefit_for_period(
                        benefits.amount,
                        GREATEST(benefits.start_date, ?),
                        LEAST(COALESCE(benefits.end_date, ?), ?),
                        0,
                        'WORKING_DAYS'
                    ) AS DECIMAL(18,2)
                ) AS prorated_benefit,
                calculate_salary_breakdown(
                    CAST(
                        calculate_prorated_benefit_for_period(
                            benefits.amount,
                            GREATEST(benefits.start_date, ?),
                            LEAST(COALESCE(benefits.end_date, ?), ?),
                            0,
                            'WORKING_DAYS'
                        ) AS DECIMAL(18,2)
                    ),
                    0,
                    0
                ) AS benefit_breakdown
            ", [
                // Bound parameters for benefits period boundaries:
                $startDate, $endDate, $endDate,
                // For calculate_prorated_benefit_for_period call:
                $startDate, $endDate, $endDate,
                // For breakdown function call:
                $startDate, $endDate, $endDate,
            ])
            ->whereIn('benefits.employee_id', $employeeIDs)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('benefits.start_date', '<=', $endDate)
                      ->where(function ($subQuery) use ($startDate) {
                          $subQuery->where('benefits.end_date', '>=', $startDate)
                                   ->orWhereNull('benefits.end_date');
                      });
            })
            ->groupBy('benefits.id')
            ->orderBy('benefits.employee_id')
            ->get();

        // 3. Deductions calculation
        // Process deductions similarly to benefits.
        $deductions = DB::table('deductions')
            ->join('employees', 'salaries.employee_id', '=', 'employees.id')
            ->whereNull('employees.deleted_at') 
            ->selectRaw("
                deductions.id AS deduction_id,
                deductions.employee_id,
                deductions.amount AS deduction_amount,
                deductions.start_date AS deduction_start,
                deductions.end_date AS deduction_end,
                GREATEST(deductions.start_date, ?) AS period_start,
                LEAST(COALESCE(deductions.end_date, ?), ?) AS period_end,
                CAST(
                    calculate_prorated_benefit_for_period(
                        deductions.amount,
                        GREATEST(deductions.start_date, ?),
                        LEAST(COALESCE(deductions.end_date, ?), ?),
                        0,
                        'WORKING_DAYS'
                    ) AS DECIMAL(18,2)
                ) AS prorated_deduction,
                calculate_salary_breakdown(
                    CAST(
                        calculate_prorated_benefit_for_period(
                            deductions.amount,
                            GREATEST(deductions.start_date, ?),
                            LEAST(COALESCE(deductions.end_date, ?), ?),
                            0,
                            'WORKING_DAYS'
                        ) AS DECIMAL(18,2)
                    ),
                    0,
                    0
                ) AS deduction_breakdown
            ", [
                // Bound parameters for deductions period boundaries:
                $startDate, $endDate, $endDate,
                // For calculate_prorated_benefit_for_period call:
                $startDate, $endDate, $endDate,
                // For breakdown function call:
                $startDate, $endDate, $endDate,
            ])
            ->whereIn('deductions.employee_id', $employeeIDs)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('deductions.start_date', '<=', $endDate)
                      ->where(function ($subQuery) use ($startDate) {
                          $subQuery->where('deductions.end_date', '>=', $startDate)
                                   ->orWhereNull('deductions.end_date');
                      });
            })
            ->groupBy('deductions.id')
            ->orderBy('deductions.employee_id')
            ->get();

        // 4. Return the combined data
        return [
            'salaries'   => $salaries,
            'benefits'   => $benefits,
            'deductions' => $deductions,
        ];
    }
}
