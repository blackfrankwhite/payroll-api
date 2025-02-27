<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class PayrollRepository
{
    /**
     * Retrieve aggregated payroll data for the given employees and period in one query.
     *
     * This query uses three CTEs:
     *   - salaries_cte: Returns salary rows with employee details.
     *   - monthly_adjustments_cte: Returns monthly adjustment rows for the provided adjustment IDs.
     *   - one_time_adjustments_cte: Returns one‑time adjustment rows (optionally filtered by provided one‑time deduction IDs)
     *     whose date falls in the payroll period.
     *
     * The final SELECT groups by employee_id and performs all aggregations in SQL.
     *
     * @param  array  $employeeIDs         List of employee IDs.
     * @param  string $startDate           Payroll calculation period start.
     * @param  string $endDate             Payroll calculation period end.
     * @param  array  $prorateAdjustments  List of monthly adjustment IDs (prorated).
     * @param  array  $regularAdjustments  List of monthly adjustment IDs (non‑prorated).
     * @param  array  $oneTimeDeductionIDs Optional list of one‑time adjustment IDs to filter deductions.
     * @return array                       Aggregated payroll results.
     */
    public function getPayrollData(
        array $employeeIDs, 
        $startDate, 
        $endDate, 
        $prorateAdjustments, 
        $regularAdjustments,
        $oneTimeDeductionIDs = []
    ) {
        // Build comma-separated strings for employee IDs and monthly adjustment IDs.
        $employeeIDsString = implode(',', $employeeIDs);
        $allMonthlyAdjIDs = array_merge($prorateAdjustments, $regularAdjustments);
        $allMonthlyAdjIDsString = count($allMonthlyAdjIDs) ? implode(',', $allMonthlyAdjIDs) : '0';

        // Build optional filter for one-time adjustments.
        $oneTimeFilter = "";
        if (!empty($oneTimeDeductionIDs)) {
            $oneTimeDeductionIDsString = implode(',', $oneTimeDeductionIDs);
            $oneTimeFilter = " AND ota.id IN ($oneTimeDeductionIDsString) ";
        }

        $sql = "
            WITH salaries_cte AS (
                SELECT 
                    s.employee_id,
                    'salary' AS record_type,
                    s.amount AS base,
                    NULL AS adjustment_type,
                    NULL AS adjustment_name,
                    e.position,
                    e.name,
                    e.email,
                    e.phone,
                    e.id_number,
                    e.surname,
                    e.gender,
                    e.birth_date,
                    e.bank_account,
                    e.residency,
                    e.address,
                    e.start_date AS employee_start_date,
                    e.end_date AS employee_end_date,
                    s.includes_income_tax,
                    s.includes_employee_pension,
                    e.pension
                FROM salaries s
                JOIN employees e ON s.employee_id = e.id
                WHERE s.employee_id IN ($employeeIDsString)
                  AND s.start_date <= ?
                  AND (s.end_date >= ? OR s.end_date IS NULL)
            ),
            monthly_adjustments_cte AS (
                SELECT 
                    a.employee_id,
                    'adjustment' AS record_type,
                    a.amount AS base,
                    m.type AS adjustment_type,
                    m.name AS adjustment_name,
                    NULL AS position,
                    NULL AS name,
                    NULL AS email,
                    NULL AS phone,
                    NULL AS id_number,
                    NULL AS surname,
                    NULL AS gender,
                    NULL AS birth_date,
                    NULL AS bank_account,
                    NULL AS residency,
                    NULL AS address,
                    NULL AS employee_start_date,
                    NULL AS employee_end_date,
                    a.includes_income_tax,
                    a.includes_employee_pension,
                    NULL AS pension
                FROM employee_monthly_salary_adjustments a
                JOIN monthly_salary_adjustments m ON a.monthly_salary_adjustment_id = m.id
                WHERE a.employee_id IN ($employeeIDsString)
                  AND m.id IN ($allMonthlyAdjIDsString)
                  AND a.start_date <= ?
                  AND (a.end_date >= ? OR a.end_date IS NULL)
            ),
            one_time_adjustments_cte AS (
                SELECT
                    ota.employee_id,
                    'one_time_adjustment' AS record_type,
                    ota.amount AS base,
                    ota.type AS adjustment_type,
                    COALESCE(ota.description, ota.type) AS adjustment_name,
                    NULL AS position,
                    NULL AS name,
                    NULL AS email,
                    NULL AS phone,
                    NULL AS id_number,
                    NULL AS surname,
                    NULL AS gender,
                    NULL AS birth_date,
                    NULL AS bank_account,
                    NULL AS residency,
                    NULL AS address,
                    NULL AS employee_start_date,
                    NULL AS employee_end_date,
                    ota.includes_income_tax,
                    ota.includes_employee_pension,
                    NULL AS pension
                FROM one_time_adjustments ota
                WHERE ota.employee_id IN ($employeeIDsString)
                  AND ota.date BETWEEN ? AND ?
                  " . $oneTimeFilter . "
            ),
            unified AS (
                SELECT * FROM salaries_cte
                UNION ALL
                SELECT * FROM monthly_adjustments_cte
                UNION ALL
                SELECT * FROM one_time_adjustments_cte
            )
            SELECT
                employee_id,
                SUM(CASE WHEN record_type = 'salary' THEN base ELSE 0 END) AS salary_gross,
                SUM(CASE WHEN record_type = 'adjustment' AND adjustment_type = 'benefit' THEN base ELSE 0 END) AS sum_benefits_gross,
                SUM(CASE WHEN record_type = 'adjustment' AND adjustment_type = 'deduction' THEN base ELSE 0 END) AS sum_deductions_gross,
                SUM(CASE WHEN record_type = 'one_time_adjustment' AND adjustment_type = 'benefit' THEN base ELSE 0 END) AS one_time_benefit_gross,
                SUM(CASE WHEN record_type = 'one_time_adjustment' AND adjustment_type = 'deduction' THEN base ELSE 0 END) AS one_time_deduction_gross,
                SUM(CASE WHEN record_type = 'salary' THEN base ELSE 0 END)
                  + SUM(CASE WHEN record_type = 'adjustment' AND adjustment_type = 'benefit' THEN base ELSE 0 END)
                  + SUM(CASE WHEN record_type = 'one_time_adjustment' AND adjustment_type = 'benefit' THEN base ELSE 0 END)
                  - (SUM(CASE WHEN record_type = 'adjustment' AND adjustment_type = 'deduction' THEN base ELSE 0 END)
                     + SUM(CASE WHEN record_type = 'one_time_adjustment' AND adjustment_type = 'deduction' THEN base ELSE 0 END)
                    ) AS sum_amount,
                (
                    SELECT JSON_ARRAYAGG(JSON_OBJECT('name', u2.adjustment_name, 'gross', u2.base))
                    FROM unified u2
                    WHERE u2.employee_id = unified.employee_id
                      AND u2.record_type = 'adjustment'
                      AND u2.adjustment_type = 'benefit'
                ) AS benefits,
                (
                    SELECT JSON_ARRAYAGG(JSON_OBJECT('name', u2.adjustment_name, 'gross', u2.base))
                    FROM unified u2
                    WHERE u2.employee_id = unified.employee_id
                      AND u2.record_type = 'adjustment'
                      AND u2.adjustment_type = 'deduction'
                ) AS deductions,
                (
                    SELECT JSON_ARRAYAGG(JSON_OBJECT('name', u2.adjustment_name, 'gross', u2.base))
                    FROM unified u2
                    WHERE u2.employee_id = unified.employee_id
                      AND u2.record_type = 'one_time_adjustment'
                      AND u2.adjustment_type = 'benefit'
                ) AS one_time_benefits,
                (
                    SELECT JSON_ARRAYAGG(JSON_OBJECT('name', u2.adjustment_name, 'gross', u2.base))
                    FROM unified u2
                    WHERE u2.employee_id = unified.employee_id
                      AND u2.record_type = 'one_time_adjustment'
                      AND u2.adjustment_type = 'deduction'
                ) AS one_time_deductions,
                MAX(position) AS position,
                MAX(name) AS name,
                MAX(email) AS email,
                MAX(phone) AS phone,
                MAX(id_number) AS id_number,
                MAX(surname) AS surname,
                MAX(gender) AS gender,
                MAX(birth_date) AS birth_date,
                MAX(bank_account) AS bank_account,
                MAX(residency) AS residency,
                MAX(address) AS address,
                MAX(employee_start_date) AS employee_start_date,
                MAX(employee_end_date) AS employee_end_date,
                MAX(includes_income_tax) AS includes_income_tax,
                MAX(includes_employee_pension) AS includes_employee_pension,
                MAX(pension) AS pension
            FROM unified
            GROUP BY employee_id
            ORDER BY employee_id;
        ";

        // There are 6 placeholders:
        // (1) s.start_date <= ?  -> $endDate
        // (2) s.end_date >= ?    -> $startDate
        // (3) a.start_date <= ?  -> $endDate
        // (4) a.end_date >= ?    -> $startDate
        // (5) ota.date BETWEEN ? -> $startDate
        // (6) and ?              -> $endDate
        $bindings = [
            $endDate, $startDate,
            $endDate, $startDate,
            $startDate, $endDate
        ];

        return DB::select($sql, $bindings);
    }
}
