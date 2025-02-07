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
            ->whereNull('salaries.deleted_at')
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

        $benefits = DB::table('salaries')
            ->join('benefits', function ($join) use ($startDate, $endDate) {
                $join->on('salaries.start_date', '<=', 'benefits.end_date')
                     ->on('salaries.end_date', '>=', 'benefits.start_date')
                     ->on('salaries.employee_id', '=', 'benefits.employee_id');
            })
            ->whereIn('salaries.employee_id', $employeeIDs)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('salaries.start_date', [$startDate, $endDate])
                      ->orWhereBetween('salaries.end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('salaries.start_date', '<', $startDate)
                            ->where('salaries.end_date', '>', $endDate);
                      });
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('benefits.start_date', [$startDate, $endDate])
                      ->orWhereBetween('benefits.end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('benefits.start_date', '<', $startDate)
                            ->where('benefits.end_date', '>', $endDate);
                      });
            })
            ->select(
                DB::raw('GREATEST(salaries.start_date, benefits.start_date, "' . $startDate . '") as period_start'),
                DB::raw('LEAST(salaries.end_date, benefits.end_date, "' . $endDate . '") as period_end'),
                'salaries.id as salary_id',
                'salaries.employee_id',
                DB::raw('calculate_salary_breakdown(
                    CAST(
                        calculate_prorated_salary_for_period(
                            salaries.amount,
                            GREATEST(salaries.start_date, "' . $startDate . '"),
                            LEAST(COALESCE(salaries.end_date, "' . $endDate . '"), "' . $endDate . '"),
                            salaries.id,
                            salaries.daily_salary_calculation_base
                        ) AS DECIMAL(18,2)
                    ),
                    salaries.includes_income_tax,
                    salaries.includes_employee_pension
                ) AS benefit_breakdown')
            )
            ->havingRaw('period_start <= period_end')
            ->orderBy('salaries.employee_id')
            ->orderBy('period_start')
            ->get();        

        // 3. Deductions calculation
        // Process deductions similarly to benefits.
        $deductions = DB::table('salaries')
            ->join('deductions', function ($join) use ($startDate, $endDate) {
                $join->on('salaries.start_date', '<=', 'deductions.end_date')
                    ->on('salaries.end_date', '>=', 'deductions.start_date')
                    ->on('salaries.employee_id', '=', 'deductions.employee_id');
            })
            ->whereIn('salaries.employee_id', $employeeIDs)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('salaries.start_date', [$startDate, $endDate])
                    ->orWhereBetween('salaries.end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('salaries.start_date', '<', $startDate)
                            ->where('salaries.end_date', '>', $endDate);
                    });
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('deductions.start_date', [$startDate, $endDate])
                    ->orWhereBetween('deductions.end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('deductions.start_date', '<', $startDate)
                            ->where('deductions.end_date', '>', $endDate);
                    });
            })
            ->select(
                DB::raw('GREATEST(salaries.start_date, deductions.start_date, "' . $startDate . '") as period_start'),
                DB::raw('LEAST(salaries.end_date, deductions.end_date, "' . $endDate . '") as period_end'),
                'salaries.id as salary_id',
                'salaries.employee_id',
                DB::raw('calculate_salary_breakdown(
                    CAST(
                        calculate_prorated_salary_for_period(
                            salaries.amount,
                            GREATEST(salaries.start_date, "' . $startDate . '"),
                            LEAST(COALESCE(salaries.end_date, "' . $endDate . '"), "' . $endDate . '"),
                            salaries.id,
                            salaries.daily_salary_calculation_base
                        ) AS DECIMAL(18,2)
                    ),
                    salaries.includes_income_tax,
                    salaries.includes_employee_pension
                ) AS deduction_breakdown')
            )
            ->havingRaw('period_start <= period_end')
            ->orderBy('salaries.employee_id')
            ->orderBy('period_start')
            ->get();    

        // 4. Return the combined data
        return [
            'salaries'   => $salaries,
            'benefits'   => $benefits,
            'deductions' => $deductions,
        ];
    }
}
