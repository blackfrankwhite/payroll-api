<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class PayrollRepository
{
    /**
     * Retrieve payroll data for the given employees and period.
     *
     * @param  array   $employeeIDs
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  bool    $prorateAdjustments
     * @param  bool    $regularAdjustments
     * @return array
     */
    public function getPayrollData(array $employeeIDs, $startDate, $endDate, $prorateAdjustments, $regularAdjustments)
    {
        $salaries = $this->getSalaries($employeeIDs, $startDate, $endDate);
        $proratedAdjustmentData = [];
        $regularAdjustmentData = [];
        if (!empty($prorateAdjustments)) {
            $proratedAdjustmentData = $this->getProratedAdjustments($employeeIDs, $startDate, $endDate, $prorateAdjustments);
        }

        if (!empty($regularAdjustments)) {
            $regularAdjustmentData = $this->getNonProratedAdjustments($employeeIDs, $startDate, $endDate, $regularAdjustments);
        }

        $adjustments = collect($proratedAdjustmentData)->merge($regularAdjustmentData)->all();
        
        return [
            'salaries'    => $salaries,
            'adjustments' => $adjustments,
        ];
    }

    /**
     * Build and execute the query to retrieve salary records.
     *
     * @param  array   $employeeIDs
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getSalaries(array $employeeIDs, $startDate, $endDate)
    {
        return DB::table('salaries')
            ->join('employees', 'salaries.employee_id', '=', 'employees.id')
            ->whereNull('employees.deleted_at')
            ->where('employees.start_date', '<=', $endDate)
            ->where(function ($query) use ($startDate) {
                $query->where('employees.end_date', '>=', $startDate)
                      ->orWhereNull('employees.end_date');
            })
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
                
                -- Employee Details
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
        
                -- Effective salary period (capped by salary, employee, and calculation period)
                GREATEST(salaries.start_date, employees.start_date, ?) AS period_start,
                LEAST(
                    COALESCE(salaries.end_date, ?),
                    COALESCE(employees.end_date, ?),
                    ?
                ) AS period_end,
                
                -- Prorated salary breakdown using the correct dates:
                calculate_salary_breakdown(
                    CAST(
                        calculate_prorated_salary_for_period(
                            salaries.amount,
                            GREATEST(salaries.start_date, employees.start_date, ?),
                            LEAST(
                                COALESCE(salaries.end_date, ?),
                                COALESCE(employees.end_date, ?),
                                ?
                            ),
                            salaries.id,
                            salaries.daily_salary_calculation_base
                        ) AS DECIMAL(18,2)
                    ),
                    salaries.includes_income_tax,
                    salaries.includes_employee_pension,
                    employees.pension
                ) AS salary_breakdown
            ", [
                $startDate,   
                $endDate, 
                $endDate,
                $endDate,
    
                $startDate,
                $endDate,
                $endDate,
                $endDate,
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
    }    

    /**
     * Build and execute the query for prorated salary adjustments.
     *
     * @param  array   $employeeIDs
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  array   $adjusmentIDs
     * @return array
     */
    private function getProratedAdjustments(array $employeeIDs, $startDate, $endDate, $adjusmentIDs)
    {
        $employeeIDsString = implode(',', $employeeIDs);
        $adjusmentIDsString = implode(',', $adjusmentIDs);

        $sql = "
            WITH salary_periods AS (
                SELECT 
                    s.id AS salary_id, 
                    s.employee_id, 
                    GREATEST(s.start_date, e.start_date, ?) AS salary_start, 
                    LEAST(COALESCE(s.end_date, e.end_date, ?), ?) AS salary_end, 
                    s.daily_salary_calculation_base,
                    e.pension
                FROM salaries s
                JOIN employees e 
                    ON s.employee_id = e.id
                WHERE s.employee_id IN ($employeeIDsString)
                  AND s.start_date <= ? 
                  AND (s.end_date IS NULL OR s.end_date >= ?)
            ),
            adjustment_periods AS (
                SELECT 
                    a.id AS adjustment_id,
                    a.employee_id, 
                    GREATEST(a.start_date, e.start_date, ?) AS adj_start, 
                    LEAST(COALESCE(a.end_date, e.end_date, ?), ?) AS adj_end, 
                    a.amount, 
                    a.includes_income_tax, 
                    a.includes_employee_pension, 
                    m.name AS adjustment_name, 
                    m.type AS adjustment_type
                FROM employee_monthly_salary_adjustments a
                JOIN monthly_salary_adjustments m 
                    ON a.monthly_salary_adjustment_id = m.id
                JOIN employees e 
                    ON a.employee_id = e.id
                WHERE a.employee_id IN ($employeeIDsString)
                  AND m.id in ($adjusmentIDsString)
                  AND a.start_date <= ? 
                  AND (a.end_date IS NULL OR a.end_date >= ?)
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
                
                calculate_prorated_adjustment_for_period(
                    ap.amount,
                    GREATEST(ap.adj_start, sp.salary_start),
                    LEAST(ap.adj_end, sp.salary_end),
                    sp.salary_id,
                    sp.daily_salary_calculation_base
                ) AS prorated_adjustment,
        
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
                    ap.includes_employee_pension,
                    sp.pension
                ) AS breakdown
            FROM adjustment_periods ap
            JOIN salary_periods sp 
              ON ap.employee_id = sp.employee_id
             AND ap.adj_start <= sp.salary_end 
             AND ap.adj_end >= sp.salary_start
            ORDER BY ap.employee_id, start_date
        ";

        return DB::select($sql, [
            // For salary_periods:
            $startDate, $endDate, $endDate, $endDate, $startDate,
            // For adjustment_periods:
            $startDate, $endDate, $endDate, $endDate, $startDate,
        ]);
    }

    /**
     * Build and execute the query for non-prorated salary adjustments.
     *
     * @param  array   $employeeIDs
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  array   $adjusmentIDs
     * @return array
     */
    private function getNonProratedAdjustments(array $employeeIDs, $startDate, $endDate, $adjusmentIDs)
    {
        $emsas = DB::table('employee_monthly_salary_adjustments as emsa')
            ->join('monthly_salary_adjustments as m', 'emsa.monthly_salary_adjustment_id', '=', 'm.id')
            ->join('employees as e', 'emsa.employee_id', '=', 'e.id')
            ->whereIn('m.id', $adjusmentIDs)
            ->selectRaw("
                emsa.employee_id,
        
                GREATEST(emsa.start_date, ?, e.start_date) AS start_date,
        
                LEAST(COALESCE(emsa.end_date, ?), ?, e.end_date) AS end_date,
        
                FLOOR(DATEDIFF(
                    LEAST(COALESCE(emsa.end_date, ?), ?, e.end_date),
                    GREATEST(emsa.start_date, ?, e.start_date)
                ) / 30.0) AS months_in_period,
        
                calculate_salary_breakdown(emsa.amount, emsa.includes_income_tax, emsa.includes_employee_pension, e.pension) as breakdown,
        
                JSON_SET(
                    calculate_salary_breakdown(emsa.amount, emsa.includes_income_tax, emsa.includes_employee_pension, e.pension),
                    '$.base', JSON_UNQUOTE(JSON_EXTRACT(
                        calculate_salary_breakdown(emsa.amount, emsa.includes_income_tax, emsa.includes_employee_pension, e.pension), '$.base'
                    )) * FLOOR(DATEDIFF(
                        LEAST(COALESCE(emsa.end_date, ?), ?, e.end_date),
                        GREATEST(emsa.start_date, ?, e.start_date)
                    ) / 30.0),
                    
                    '$.net', JSON_UNQUOTE(JSON_EXTRACT(
                        calculate_salary_breakdown(emsa.amount, emsa.includes_income_tax, emsa.includes_employee_pension, e.pension), '$.net'
                    )) * FLOOR(DATEDIFF(
                        LEAST(COALESCE(emsa.end_date, ?), ?, e.end_date),
                        GREATEST(emsa.start_date, ?, e.start_date)
                    ) / 30.0),
                    
                    '$.pension', JSON_UNQUOTE(JSON_EXTRACT(
                        calculate_salary_breakdown(emsa.amount, emsa.includes_income_tax, emsa.includes_employee_pension, e.pension), '$.pension'
                    )) * FLOOR(DATEDIFF(
                        LEAST(COALESCE(emsa.end_date, ?), ?, e.end_date),
                        GREATEST(emsa.start_date, ?, e.start_date)
                    ) / 30.0),
        
                    '$.income_tax', JSON_UNQUOTE(JSON_EXTRACT(
                        calculate_salary_breakdown(emsa.amount, emsa.includes_income_tax, emsa.includes_employee_pension, e.pension), '$.income_tax'
                    )) * FLOOR(DATEDIFF(
                        LEAST(COALESCE(emsa.end_date, ?), ?, e.end_date),
                        GREATEST(emsa.start_date, ?, e.start_date)
                    ) / 30.0)
                ) AS adjustment_breakdown,
        
                emsa.includes_income_tax,
                emsa.includes_employee_pension,
                m.name AS adjustment_name,
                m.type AS adjustment_type,
                emsa.payment_currency,
                emsa.calculation_currency
            ", [
                $startDate,  
                $endDate,    
                $endDate,    
                $endDate,    
                $endDate,    
                $startDate,  
                $endDate,    
                $endDate,    
                $startDate,  
                $endDate,    
                $endDate,    
                $startDate,  
                $endDate,    
                $endDate,    
                $startDate,  
                $endDate,    
                $endDate,    
                $startDate,
            ])
            ->whereIn('emsa.employee_id', $employeeIDs)
            ->where('emsa.start_date', '<=', $endDate)
            ->where(function ($query) use ($startDate) {
                $query->where('emsa.end_date', '>=', $startDate)
                      ->orWhereNull('emsa.end_date');
            })
            ->get();
                
        return $emsas;        
    }
}
