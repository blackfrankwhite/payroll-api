<?php

namespace App\Repositories;

use App\Models\IncomeTaxExemption;

class IncomeTaxExemptionRepository
{
    public function create(array $data)
    {
        return IncomeTaxExemption::create($data);
    }

    public function getByEmployee(int $employeeID)
    {
        return IncomeTaxExemption::where('employee_id', $employeeID)->get();
    }

    public function getById(int $employeeID, int $incomeTaxExemptionID)
    {
        return IncomeTaxExemption::where('employee_id', $employeeID)->where('id', $incomeTaxExemptionID)->firstOrFail();
    }

    public function update(int $employeeID, int $incomeTaxExemptionID, array $data)
    {
        $incomeTaxExemption = $this->getById($employeeID, $incomeTaxExemptionID);
        $incomeTaxExemption->update($data);
        return $incomeTaxExemption;
    }

    public function delete(int $employeeID, int $incomeTaxExemptionID)
    {
        $incomeTaxExemption = $this->getById($employeeID, $incomeTaxExemptionID);
        $incomeTaxExemption->delete();
    }
}