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
                
                -- Fetch Employee Details
                employees.position,
                employees.name,
                employees.email,
                employees.phone,
                employees.id_number,
                employees.surname,
                employees.gender,
                employees.birth_date,
                employees.bank_account,
                employees.residency,
                employees.address,
                employees.start_date AS employee_start_date,
                employees.end_date AS employee_end_date,
                employees.pension,
        
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
    

        $adjustments = DB::select("
            WITH salary_periods AS (
                SELECT 
                    s.id AS salary_id, 
                    s.employee_id, 
                    GREATEST(s.start_date, ?) AS salary_start, 
                    LEAST(COALESCE(s.end_date, ?), ?) AS salary_end, 
                    s.daily_salary_calculation_base
                FROM salaries s
                WHERE s.employee_id IN (" . implode(',', $employeeIDs) . ")
                AND (
                    s.start_date <= ? 
                    AND (s.end_date IS NULL OR s.end_date >= ?)
                )
            ),
            adjustment_periods AS (
                SELECT 
                    a.id AS adjustment_id,
                    a.employee_id, 
                    GREATEST(a.start_date, ?) AS adj_start, 
                    LEAST(COALESCE(a.end_date, ?), ?) AS adj_end, 
                    a.amount, 
                    a.includes_income_tax, 
                    a.includes_employee_pension, 
                    m.name AS adjustment_name, 
                    m.type AS adjustment_type
                FROM employee_monthly_salary_adjustments a
                JOIN monthly_salary_adjustments m ON a.monthly_salary_adjustment_id = m.id
                WHERE a.employee_id IN (" . implode(',', $employeeIDs) . ")
                AND (
                    a.start_date <= ? 
                    AND (a.end_date IS NULL OR a.end_date >= ?)
                )
            )
            SELECT 
                ap.employee_id, 
                sp.salary_id,
                GREATEST(ap.adj_start, sp.salary_start) AS start_date,
                LEAST(ap.adj_end, sp.salary_end) AS end_date,
                ap.amount,
                ap.adjustment_name,
                ap.adjustment_type,
                sp.daily_salary_calculation_base,
                ap.includes_income_tax,
                ap.includes_employee_pension,
                
                -- Calculate Prorated Adjustment
                calculate_prorated_adjustment_for_period(
                    ap.amount,
                    GREATEST(ap.adj_start, sp.salary_start),
                    LEAST(ap.adj_end, sp.salary_end),
                    sp.salary_id,
                    sp.daily_salary_calculation_base
                ) AS prorated_adjustment,
        
                -- Calculate Salary Breakdown based on the Prorated Adjustment
                calculate_salary_breakdown(
                    CAST(
                        calculate_prorated_adjustment_for_period(
                            ap.amount,
                            GREATEST(ap.adj_start, sp.salary_start),
                            LEAST(ap.adj_end, sp.salary_end),
                            sp.salary_id,
                            sp.daily_salary_calculation_base
                        ) AS DECIMAL(18,2)
                    ),
                    ap.includes_income_tax,
                    ap.includes_employee_pension
                ) AS adjustment_breakdown
        
            FROM adjustment_periods ap
            JOIN salary_periods sp 
                ON ap.employee_id = sp.employee_id
                AND ap.adj_start <= sp.salary_end 
                AND ap.adj_end >= sp.salary_start
            ORDER BY ap.employee_id, start_date", [
                $startDate, $endDate, $endDate, $endDate, $startDate, 
                $startDate, $endDate, $endDate, $endDate, $startDate
            ]);                
        
        // 4. Return the combined data
        return [
            'salaries'   => $salaries,
            'adjustments'   => $adjustments
        ];
    }
}
