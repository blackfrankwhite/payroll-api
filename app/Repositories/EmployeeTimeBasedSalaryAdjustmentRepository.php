<?php

namespace App\Repositories;

use App\Models\EmployeeTimeBasedSalaryAdjustment;

class EmployeeTimeBasedSalaryAdjustmentRepository
{
    public function getAllForEmployee(int $companyId, int $employeeId)
    {
        return EmployeeTimeBasedSalaryAdjustment::with('dates')
            ->join('time_based_salary_adjustments', 'employee_time_based_salary_adjustments.time_based_salary_adjustment_id', '=', 'time_based_salary_adjustments.id')
            ->select(
                'employee_time_based_salary_adjustments.*', 
                'time_based_salary_adjustments.name', 
                'time_based_salary_adjustments.type'
            )
            ->whereHas('employee', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->where('employee_time_based_salary_adjustments.employee_id', $employeeId)
            ->get();
    }

    public function find(int $companyId, int $employeeId, int $id)
    {
        return EmployeeTimeBasedSalaryAdjustment::with('dates')
            ->join('time_based_salary_adjustments', 'employee_time_based_salary_adjustments.time_based_salary_adjustment_id', '=', 'time_based_salary_adjustments.id')
            ->select(
                'employee_time_based_salary_adjustments.*', 
                'time_based_salary_adjustments.name', 
                'time_based_salary_adjustments.type'
            )
            ->whereHas('employee', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->where('employee_time_based_salary_adjustments.employee_id', $employeeId)
            ->where('employee_time_based_salary_adjustments.id', $id)
            ->firstOrFail();
    }

    public function create(int $companyId, int $employeeId, array $data)
    {
        // Ensure the main record gets the employee_id.
        $data['employee_id'] = $employeeId;
        // Extract the dates array and remove it from $data.
        $dates = $data['dates'];
        unset($data['dates']);

        $adjustment = EmployeeTimeBasedSalaryAdjustment::create($data);

        // Insert each date into the related table.
        foreach ($dates as $date) {
            $adjustment->dates()->create(['date' => $date]);
        }

        return $adjustment->load('dates');
    }

    public function update(int $companyId, int $employeeId, int $id, array $data)
    {
        $adjustment = $this->find($companyId, $employeeId, $id);
        if (isset($data['dates'])) {
            $dates = $data['dates'];
            unset($data['dates']);
            // Remove old date records.
            $adjustment->dates()->delete();
            // Insert new dates.
            foreach ($dates as $date) {
                $adjustment->dates()->create(['date' => $date]);
            }
        }
        $adjustment->update($data);
        return $adjustment->load('dates');
    }

    public function delete(int $companyId, int $employeeId, int $id)
    {
        $adjustment = $this->find($companyId, $employeeId, $id);
        // Delete associated dates first.
        $adjustment->dates()->delete();
        $adjustment->delete();
    }
}
