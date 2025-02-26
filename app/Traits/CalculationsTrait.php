<?php

namespace App\Traits;

trait CalculationsTrait
{
    /**
     * Calculate a salary breakdown from a given aggregated gross amount.
     *
     * @param float $gross
     * @param bool  $includes_income_tax
     * @param bool  $includes_employee_pension
     * @param bool  $pension  // Employee's pension flag
     * @return array
     */
    public function calculateBreakdown(float $gross, bool $includes_income_tax, bool $includes_employee_pension, bool $pension): array
    {
        // Set coefficients based on the employee's pension flag.
        if ($pension) {
            $pension_co    = 0.02;
            $income_tax_co = 0.196;
            $net_co        = 0.784;
        } else {
            $pension_co    = 0;
            $income_tax_co = 0.2;
            $net_co        = 0.8;
        }

        // Determine the base amount.
        if ($includes_income_tax && $includes_employee_pension) {
            $base = $gross;
        } elseif ($includes_employee_pension) {
            $base = $gross / ($net_co + $pension_co);
        } elseif ($includes_income_tax) {
            $base = $gross / ($net_co + $income_tax_co);
        } else {
            $base = $gross / $net_co;
        }

        return [
            'base'        => round($base, 2),
            'pension'     => round($base * $pension_co, 2),
            'income_tax'  => round($base * $income_tax_co, 2),
            'net'         => round($base * $net_co, 2),
        ];
    }
}
