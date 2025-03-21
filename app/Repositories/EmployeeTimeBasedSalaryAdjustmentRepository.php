<?php

namespace App\Repositories;

use App\Models\EmployeeTimeBasedSalaryAdjustment;

class EmployeeTimeBasedSalaryAdjustmentRepository
{
    public function getAllForEmployee(int $companyId, int $employeeId)
    {
        return EmployeeTimeBasedSalaryAdjustment::join('time_based_salary_adjustments', 'employee_time_based_salary_adjustments.time_based_salary_adjustment_id', '=', 'time_based_salary_adjustments.id')
            ->select(
                'employee_time_based_salary_adjustments.*', 
                'time_based_salary_adjustments.name', 
                'time_based_salary_adjustments.type',
                'time_based_salary_adjustments.percent'
            )
            ->whereHas('employee', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->where('employee_time_based_salary_adjustments.employee_id', $employeeId)
            ->get();
    }

    public function find(int $companyId, int $employeeId, int $id)
    {
        return EmployeeTimeBasedSalaryAdjustment::join('time_based_salary_adjustments', 'employee_time_based_salary_adjustments.time_based_salary_adjustment_id', '=', 'time_based_salary_adjustments.id')
            ->select(
                'employee_time_based_salary_adjustments.*', 
                'time_based_salary_adjustments.name', 
                'time_based_salary_adjustments.type',
                'time_based_salary_adjustments.percent'
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
        $data['employee_id'] = $employeeId;
        return EmployeeTimeBasedSalaryAdjustment::create($data);
    }

    public function update(int $companyId, int $employeeId, int $id, array $data)
    {
        $adjustment = $this->find($companyId, $employeeId, $id);
        $adjustment->update($data);
        return $adjustment;
    }

    public function delete(int $companyId, int $employeeId, int $id)
    {
        $adjustment = $this->find($companyId, $employeeId, $id);
        $adjustment->delete();
    }
}
