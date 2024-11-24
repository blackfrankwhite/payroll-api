<?php

namespace App\Repositories;

use App\Models\Benefit;

class BenefitRepository
{
    public function create(array $data)
    {
        return Benefit::create($data);
    }

    public function getByEmployee(int $employeeID)
    {
        return Benefit::where('employee_id', $employeeID)->get();
    }

    public function getById(int $employeeID, int $benefitID)
    {
        return Benefit::where('employee_id', $employeeID)
            ->where('id', $benefitID)
            ->firstOrFail();
    }

    public function update(int $employeeID, int $benefitID, array $data)
    {
        $benefit = $this->getById($employeeID, $benefitID);
        $benefit->update($data);

        return $benefit;
    }

    public function delete(int $employeeID, int $benefitID)
    {
        $benefit = $this->getById($employeeID, $benefitID);
        $benefit->delete();
    }
}
