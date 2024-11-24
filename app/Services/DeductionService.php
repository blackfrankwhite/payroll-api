<?php

namespace App\Services;

use App\Repositories\DeductionRepository;
use App\Repositories\EmployeeRepository;

class DeductionService
{
    protected $deductionRepository;
    protected $employeeRepository;

    public function __construct(DeductionRepository $deductionRepository, EmployeeRepository $employeeRepository)
    {
        $this->deductionRepository = $deductionRepository;
        $this->employeeRepository = $employeeRepository;
    }

    public function addDeduction(int $userId, int $employeeID, array $data)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);
        $data['employee_id'] = $employee->id;

        return $this->deductionRepository->create($data);
    }

    public function getDeductions(int $userId, int $employeeID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->deductionRepository->getByEmployee($employee->id);
    }

    public function getDeduction(int $userId, int $employeeID, int $deductionID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->deductionRepository->getById($employee->id, $deductionID);
    }

    public function updateDeduction(int $userId, int $employeeID, int $deductionID, array $data)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->deductionRepository->update($employee->id, $deductionID, $data);
    }

    public function deleteDeduction(int $userId, int $employeeID, int $deductionID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        $this->deductionRepository->delete($employee->id, $deductionID);
    }
}
