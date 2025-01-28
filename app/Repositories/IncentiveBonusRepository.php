<?php

namespace App\Repositories;

use App\Models\IncentiveBonus;

class IncentiveBonusRepository
{
    public function create(array $data)
    {
        return IncentiveBonus::create($data);
    }

    public function getByEmployee(int $employeeID)
    {
        return IncentiveBonus::where('employee_id', $employeeID)->get();
    }

    public function getById(int $employeeID, int $incentiveBonusID)
    {
        return IncentiveBonus::where('employee_id', $employeeID)->where('id', $incentiveBonusID)->firstOrFail();
    }

    public function update(int $employeeID, int $incentiveBonusID, array $data)
    {
        $incentiveBonus = $this->getById($employeeID, $incentiveBonusID);
        $incentiveBonus->update($data);
        return $incentiveBonus;
    }

    public function delete(int $employeeID, int $incentiveBonusID)
    {
        $incentiveBonus = $this->getById($employeeID, $incentiveBonusID);
        $incentiveBonus->delete();
    }
}