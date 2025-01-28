<?php

namespace App\Services;

use App\Repositories\IncentiveBonusRepository;
use App\Repositories\EmployeeRepository;

class IncentiveBonusService
{
    protected $incentiveBonusRepository;
    protected $employeeRepository;

    public function __construct(IncentiveBonusRepository $incentiveBonusRepository, EmployeeRepository $employeeRepository)
    {
        $this->incentiveBonusRepository = $incentiveBonusRepository;
        $this->employeeRepository = $employeeRepository;
    }

    public function addIncentiveBonus(int $userId, int $employeeID, array $data)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);
        $data['employee_id'] = $employee->id;

        return $this->incentiveBonusRepository->create($data);
    }

    public function getIncentiveBonuses(int $userId, int $employeeID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->incentiveBonusRepository->getByEmployee($employee->id);
    }

    public function getIncentiveBonus(int $userId, int $employeeID, int $incentiveBonusID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->incentiveBonusRepository->getById($employee->id, $incentiveBonusID);
    }

    public function updateIncentiveBonus(int $userId, int $employeeID, int $incentiveBonusID, array $data)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->incentiveBonusRepository->update($employee->id, $incentiveBonusID, $data);
    }

    public function deleteIncentiveBonus(int $userId, int $employeeID, int $incentiveBonusID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        $this->incentiveBonusRepository->delete($employee->id, $incentiveBonusID);
    }
}