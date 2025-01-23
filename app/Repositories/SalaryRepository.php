<?php

namespace App\Repositories;

use App\Models\Salary;

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
}
