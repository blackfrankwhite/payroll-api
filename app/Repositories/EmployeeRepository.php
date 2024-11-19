<?php

namespace App\Repositories;

use App\Models\Employee;

class EmployeeRepository
{
    public function addEmployee(array $employeeData)
    {
        return Employee::create($employeeData);
    }

    public function getEmployeesByCompanyId(int $companyId)
    {
        return Employee::where('company_id', $companyId)->get();
    }

    public function updateEmployee(int $companyId, int $employeeID, array $employeeData)
    {
        $employee = $this->findEmployee($companyId, $employeeID);
        $employee->update($employeeData);

        return $employee;
    }

    public function deleteEmployee(int $companyId, int $employeeID)
    {
        $employee = $this->findEmployee($companyId, $employeeID);
        $employee->delete();
    }

    public function getEmployeeById(int $companyId, int $employeeID)
    {
        return $this->findEmployee($companyId, $employeeID);
    }

    private function findEmployee(int $companyId, int $employeeID)
    {
        return Employee::where('company_id', $companyId)
            ->where('id', $employeeID)
            ->firstOrFail();
    }

    public function getEmployeeByIdAndUser(int $employeeID, int $userId)
    {
        return Employee::where('id', $employeeID)
            ->where('company_id', function ($query) use ($userId) {
                $query->select('company_id')
                    ->from('company_users')
                    ->where('user_id', $userId);
            })
            ->firstOrFail();
    }
}
