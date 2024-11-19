<?php

namespace App\Services;

use App\Repositories\EmployeeRepository;
use App\Repositories\CompanyUserRepository;

class EmployeeService
{
    protected $employeeRepository;
    protected $companyUserRepository;

    public function __construct(EmployeeRepository $employeeRepository, CompanyUserRepository $companyUserRepository)
    {
        $this->employeeRepository = $employeeRepository;
        $this->companyUserRepository = $companyUserRepository;
    }

    public function addEmployee(int $userId, array $employeeData)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        $employeeData['company_id'] = $companyUser->company_id;

        return $this->employeeRepository->addEmployee($employeeData);
    }

    public function getEmployees(int $userId)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);

        return $this->employeeRepository->getEmployeesByCompanyId($companyUser->company_id);
    }

    public function updateEmployee(int $userId, int $employeeID, array $employeeData)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);

        return $this->employeeRepository->updateEmployee($companyUser->company_id, $employeeID, $employeeData);
    }

    public function deleteEmployee(int $userId, int $employeeID)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);

        $this->employeeRepository->deleteEmployee($companyUser->company_id, $employeeID);
    }

    public function getEmployeeByID(int $userId, int $employeeID)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);

        return $this->employeeRepository->getEmployeeById($companyUser->company_id, $employeeID);
    }
}
