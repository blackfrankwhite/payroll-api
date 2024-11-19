<?php

namespace App\Repositories;

use App\Models\Salary;

class SalaryRepository
{
    public function create(array $data)
    {
        return Salary::create($data);
    }

    public function getByEmployee(int $employeeID)
    {
        return Salary::where('employee_id', $employeeID)->get();
    }

    public function getById(int $employeeID, int $salaryID)
    {
        return Salary::where('employee_id', $employeeID)
            ->where('id', $salaryID)
            ->firstOrFail();
    }

    public function update(int $employeeID, int $salaryID, array $data)
    {
        $salary = $this->getById($employeeID, $salaryID);
        $salary->update($data);

        return $salary;
    }

    public function delete(int $employeeID, int $salaryID)
    {
        $salary = $this->getById($employeeID, $salaryID);
        $salary->delete();
    }
}
