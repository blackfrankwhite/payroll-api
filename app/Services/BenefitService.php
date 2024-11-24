<?php

namespace App\Services;

use App\Repositories\BenefitRepository;
use App\Repositories\EmployeeRepository;

class BenefitService
{
    protected $benefitRepository;
    protected $employeeRepository;

    public function __construct(BenefitRepository $benefitRepository, EmployeeRepository $employeeRepository)
    {
        $this->benefitRepository = $benefitRepository;
        $this->employeeRepository = $employeeRepository;
    }

    public function addBenefit(int $userId, int $employeeID, array $data)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);
        $data['employee_id'] = $employee->id;

        return $this->benefitRepository->create($data);
    }

    public function getBenefits(int $userId, int $employeeID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->benefitRepository->getByEmployee($employee->id);
    }

    public function getBenefit(int $userId, int $employeeID, int $benefitID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->benefitRepository->getById($employee->id, $benefitID);
    }

    public function updateBenefit(int $userId, int $employeeID, int $benefitID, array $data)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->benefitRepository->update($employee->id, $benefitID, $data);
    }

    public function deleteBenefit(int $userId, int $employeeID, int $benefitID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        $this->benefitRepository->delete($employee->id, $benefitID);
    }
}
