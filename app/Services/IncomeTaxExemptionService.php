<?php

namespace App\Services;

use App\Repositories\IncomeTaxExemptionRepository;
use App\Repositories\EmployeeRepository;

class IncomeTaxExemptionService
{
    protected $incomeTaxExemptionRepository;
    protected $employeeRepository;

    public function __construct(IncomeTaxExemptionRepository $incomeTaxExemptionRepository, EmployeeRepository $employeeRepository)
    {
        $this->incomeTaxExemptionRepository = $incomeTaxExemptionRepository;
        $this->employeeRepository = $employeeRepository;
    }

    public function addIncomeTaxExemption(int $userId, int $employeeID, array $data)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);
        $data['employee_id'] = $employee->id;

        return $this->incomeTaxExemptionRepository->create($data);
    }

    public function getIncomeTaxExemptions(int $userId, int $employeeID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->incomeTaxExemptionRepository->getByEmployee($employee->id);
    }

    public function getIncomeTaxExemption(int $userId, int $employeeID, int $incomeTaxExemptionID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->incomeTaxExemptionRepository->getById($employee->id, $incomeTaxExemptionID);
    }

    public function updateIncomeTaxExemption(int $userId, int $employeeID, int $incomeTaxExemptionID, array $data)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->incomeTaxExemptionRepository->update($employee->id, $incomeTaxExemptionID, $data);
    }

    public function deleteIncomeTaxExemption(int $userId, int $employeeID, int $incomeTaxExemptionID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        $this->incomeTaxExemptionRepository->delete($employee->id, $incomeTaxExemptionID);
    }
}