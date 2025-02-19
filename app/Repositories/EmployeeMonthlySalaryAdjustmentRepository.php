<?php

namespace App\Repositories;

use App\Models\EmployeeMonthlySalaryAdjustment;

class EmployeeMonthlySalaryAdjustmentRepository
{
    public function getAllForEmployee(int $companyId, int $employeeId)
    {
        return EmployeeMonthlySalaryAdjustment::join('monthly_salary_adjustments', 'employee_monthly_salary_adjustments.monthly_salary_adjustment_id', '=', 'monthly_salary_adjustments.id')
            ->select('employee_monthly_salary_adjustments.*', 'monthly_salary_adjustments.name', 'monthly_salary_adjustments.type')
            ->whereHas('employee', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->where('employee_id', $employeeId)
            ->get();
    }

    public function find(int $companyId, int $employeeId, int $id)
    {
        return EmployeeMonthlySalaryAdjustment::join('monthly_salary_adjustments', 'employee_monthly_salary_adjustments.monthly_salary_adjustment_id', '=', 'monthly_salary_adjustments.id')
            ->select('employee_monthly_salary_adjustments.*', 'monthly_salary_adjustments.name', 'monthly_salary_adjustments.type')
            ->whereHas('employee', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
            })
            ->where('employee_id', $employeeId)
            ->where('id', $id)
            ->firstOrFail();
    }

    public function create(int $companyId, int $employeeId, array $data)
    {
        $data['employee_id'] = $employeeId;
        return EmployeeMonthlySalaryAdjustment::create($data);
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
