<?php

namespace App\Traits;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

trait CalculationsTrait
{
    public function calculateBaseAmount(
        $amount,
        $includesIncomeTax, 
        $includesEmployeePension, 
        $includesCompanyPension,
        $startDate, // Salary period start
        $endDate,   // Salary period end
        $dailySalaryCalculationBase = 'working',
        $pensionCo = 0.02,
        $incomeTaxCo = 0.196,
        $netCo = 0.784
    ) {
        if ($includesIncomeTax && $includesEmployeePension) {
            $base = $amount * 1;
        } elseif ($includesEmployeePension) {
            $base = $amount / ($netCo + $pensionCo);
        } elseif ($includesIncomeTax) {
            $base = $amount / ($netCo + $incomeTaxCo);
        } else {
            $base = $amount / $netCo;
        }

        $baseProrated = $this->calculateProratedSalary($base, $startDate, $endDate, $dailySalaryCalculationBase);

        return $this->calculateFromBase($baseProrated, $pensionCo, $incomeTaxCo, $netCo);
    }

    private function calculateFromBase($base, $pensionCo, $incomeTaxCo, $netCo)
    {
        $pension = $base * $pensionCo;
        $incomeTax = $base * $incomeTaxCo;
        $net = $base * $netCo;

        return [
            'base' => $base,
            'pension' => $pension,
            'income_tax' => $incomeTax,
            'net' => $net
        ];
    }

    private function calculateProratedSalary($baseSalary, $startDate, $endDate, $dailySalaryCalculationBase)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
    
        // Get total days in the full month
        $monthDays = Carbon::parse($startDate)->daysInMonth;
    
        // Get actual days in the salary period
        $periodDays = $start->diffInDays($end) + 1;
    
        if ($dailySalaryCalculationBase === 'working') {
            // Clone to prevent modification of original start date
            $fullMonthStart = (clone $start)->startOfMonth();
            $fullMonthEnd = (clone $start)->endOfMonth();
    
            // Count only working days (excluding weekends)
            $workingDaysInMonth = $this->countWorkingDays($fullMonthStart, $fullMonthEnd);
            $workingDaysInPeriod = $this->countWorkingDays($start, $end);
    
            if ($workingDaysInMonth > 0) {
                return ($baseSalary / $workingDaysInMonth) * $workingDaysInPeriod;
            }
        } else {
            // Use calendar days
            return ($baseSalary / $monthDays) * $periodDays;
        }
    
        return 0; // Fallback if no valid working days are found
    }
    
    private function countWorkingDays($start, $end)
    {
        $period = CarbonPeriod::create($start, $end);
        $workingDays = 0;
    
        foreach ($period as $date) {
            if (!$this->isWeekend($date)) {
                $workingDays++;
            }
        }
    
        return $workingDays;
    }
    
    private function isWeekend($date)
    {
        return in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
    }    
}
