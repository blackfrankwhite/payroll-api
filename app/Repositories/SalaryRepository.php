<?php

namespace App\Repositories;

use App\Models\Salary;
use Carbon\Carbon;

class SalaryRepository
{
    public function create(array $data)
    {
        $salary = Salary::create($data);

        if (isset($data['non_working_days'])) {
            foreach ($data['non_working_days'] as $day) {
                $salary->nonWorkingDays()->create(['day' => $day]);
            }
        }
    
        if (isset($data['non_working_custom_dates'])) {
            foreach ($data['non_working_custom_dates'] as $customDate) {
                $salary->nonWorkingCustomDates()->create(['custom_date' => $customDate]);
            }
        }

        $salary->load(['nonWorkingDays', 'nonWorkingCustomDates']);
    
        return $salary;
    }

    public function getByEmployee(int $employeeID)
    {
        return Salary::where('employee_id', $employeeID)
            ->with(['nonWorkingDays', 'nonWorkingCustomDates'])
            ->get();
    }

    public function getById(int $employeeID, int $salaryID)
    {
        // Eager load relations for a specific salary
        return Salary::where('employee_id', $employeeID)
            ->where('id', $salaryID)
            ->with(['nonWorkingDays', 'nonWorkingCustomDates'])
            ->firstOrFail();
    }

    public function update(int $employeeID, int $salaryID, array $data)
    {
        $salary = $this->getById($employeeID, $salaryID);
        $salary->update($data);
    
        if (isset($data['non_working_days'])) {
            $salary->nonWorkingDays()->delete();
            foreach ($data['non_working_days'] as $day) {
                $salary->nonWorkingDays()->create(['day' => $day]);
            }
        }
    
        if (isset($data['non_working_custom_dates'])) {
            $salary->nonWorkingCustomDates()->delete();
            foreach ($data['non_working_custom_dates'] as $customDate) {
                $salary->nonWorkingCustomDates()->create(['custom_date' => $customDate]);
            }
        }

        $salary->load(['nonWorkingDays', 'nonWorkingCustomDates']);
    
        return $salary;
    }

    public function delete(int $employeeID, int $salaryID)
    {
        $salary = $this->getById($employeeID, $salaryID);
        $salary->delete();
    }

    public function getSalaryCoverage($startDate, $endDate, $employeeId)
    {
        $salaries = Salary::where('employee_id', $employeeId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->where(function ($subQuery) use ($startDate) {
                          $subQuery->where('end_date', '>=', $startDate)
                                   ->orWhereNull('end_date');
                      });
            })
            ->orderBy('start_date') 
            ->get();

        $intervals = [];
        foreach ($salaries as $salary) {
            // Convert to Carbon for safe comparisons and bounding
            $salaryStart = Carbon::parse($salary->start_date);
            $salaryEnd   = $salary->end_date
                ? Carbon::parse($salary->end_date)
                : Carbon::parse($endDate); // interpret null end as ongoing, clamp to $endDate

            // Clamp each interval to the query bounds
            $clampedStart = $salaryStart->lt($startDate) ? Carbon::parse($startDate) : $salaryStart;
            $clampedEnd   = $salaryEnd->gt($endDate) ? Carbon::parse($endDate) : $salaryEnd;

            // If after clamping, we still have a valid range, push to intervals
            if ($clampedStart->lte($clampedEnd)) {
                $intervals[] = [
                    'start' => $clampedStart,
                    'end'   => $clampedEnd,
                    'type' => $salary->type,
                    'amount' => $salary->amount,
                    'payment_currency' => $salary->payment_currency,
                    'calculation_currency' => $salary->calculation_currency,
                    'includes_income_tax' => $salary->includes_income_tax,
                    'includes_employee_pension' => $salary->includes_employee_pension,
                    'includes_company_pension' => $salary->includes_company_pension,
                    'daily_salary_calculation_base' => $salary->daily_salary_calculation_base,
                    'daily_working_hours' => $salary->daily_working_hours,
                ];
            }
        }

        // 3) Merge overlapping intervals in a single pass
        $merged = [];
        foreach ($intervals as $interval) {
            // If no intervals in $merged yet, or no overlap with the last one, just add
            if (empty($merged) || $merged[count($merged) - 1]['end']->lt($interval['start'])) {
                $merged[] = $interval;
            } else {
                // They overlap, so we extend the end if needed
                $merged[count($merged) - 1]['end'] = $merged[count($merged) - 1]['end']->max($interval['end']);
            }
        }

        // Convert final Carbon objects back to strings
        $coverage = array_map(function ($item) {
            return [
                'start' => $item['start']->format('Y-m-d'),
                'end'   => $item['end']->format('Y-m-d'),
                'type' => $item['type'],
                'amount' => $item['amount'],
                'payment_currency' => $item['payment_currency'],
                'calculation_currency' => $item['calculation_currency'],
                'includes_income_tax' => $item['includes_income_tax'],
                'includes_employee_pension' => $item['includes_employee_pension'],
                'includes_company_pension' => $item['includes_company_pension'],
                'daily_salary_calculation_base' => $item['daily_salary_calculation_base'],
                'daily_working_hours' => $item['daily_working_hours'],
                
            ];
        }, $merged);

        return $coverage;
    }
}
