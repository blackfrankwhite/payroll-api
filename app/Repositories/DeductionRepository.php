<?php

namespace App\Repositories;

use App\Models\Deduction;

class DeductionRepository
{
    public function create(array $data)
    {
        return Deduction::create($data);
    }

    public function getByEmployee(int $employeeID)
    {
        return Deduction::where('employee_id', $employeeID)->get();
    }

    public function getById(int $employeeID, int $deductionID)
    {
        return Deduction::where('employee_id', $employeeID)
            ->where('id', $deductionID)
            ->firstOrFail();
    }

    public function update(int $employeeID, int $deductionID, array $data)
    {
        $deduction = $this->getById($employeeID, $deductionID);
        $deduction->update($data);

        return $deduction;
    }

    public function delete(int $employeeID, int $deductionID)
    {
        $deduction = $this->getById($employeeID, $deductionID);
        $deduction->delete();
    }
}
