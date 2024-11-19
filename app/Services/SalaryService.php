<?php

namespace App\Services;

use App\Repositories\SalaryRepository;
use App\Repositories\EmployeeRepository;

class SalaryService
{
    protected $salaryRepository;
    protected $employeeRepository;

    public function __construct(SalaryRepository $salaryRepository, EmployeeRepository $employeeRepository)
    {
        $this->salaryRepository = $salaryRepository;
        $this->employeeRepository = $employeeRepository;
    }

    public function addSalary(int $userId, int $employeeID, array $data)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);
        $data['employee_id'] = $employee->id;

        return $this->salaryRepository->create($data);
    }

    public function getSalaries(int $userId, int $employeeID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->salaryRepository->getByEmployee($employee->id);
    }

    public function getSalary(int $userId, int $employeeID, int $salaryID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->salaryRepository->getById($employee->id, $salaryID);
    }

    public function updateSalary(int $userId, int $employeeID, int $salaryID, array $data)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        return $this->salaryRepository->update($employee->id, $salaryID, $data);
    }

    public function deleteSalary(int $userId, int $employeeID, int $salaryID)
    {
        $employee = $this->employeeRepository->getEmployeeByIdAndUser($employeeID, $userId);

        $this->salaryRepository->delete($employee->id, $salaryID);
    }
}
