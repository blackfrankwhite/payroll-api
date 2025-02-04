<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class PayrollRepository
{
    public function getPayrollData(array $employeeIDs, $startDate, $endDate)
    {
        $salaries = DB::table('salaries')
            ->selectRaw("
                salaries.id AS salary_id,
                salaries.employee_id,
                salaries.amount AS salary_amount,
                salaries.daily_salary_calculation_base,
                salaries.type,
                salaries.payment_currency,
                
                -- Compute the prorated salary for this salary record over the effective period,
                -- pass it along with the tax flags into the breakdown function, and return its JSON output.
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
                // For effective period boundaries:
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

        return $salaries;
    }
}
