<?php

namespace App\Services;

use App\Repositories\EmployeeMonthlySalaryAdjustmentRepository;
use App\Repositories\MonthlySalaryAdjustmentRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\CompanyUserRepository;

class EmployeeMonthlySalaryAdjustmentService
{
    protected $repository;
    protected $monthlySalaryAdjustmentRepository;
    protected $employeeRepository;
    protected $companyUserRepository;

    public function __construct(
        EmployeeMonthlySalaryAdjustmentRepository $repository,
        MonthlySalaryAdjustmentRepository $monthlySalaryAdjustmentRepository,
        EmployeeRepository $employeeRepository,
        CompanyUserRepository $companyUserRepository
    ) {
        $this->repository = $repository;
        $this->monthlySalaryAdjustmentRepository = $monthlySalaryAdjustmentRepository;
        $this->employeeRepository = $employeeRepository;
        $this->companyUserRepository = $companyUserRepository;
    }

    public function getAllForEmployee(int $userId, int $employeeId)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        return $this->repository->getAllForEmployee($companyUser->company_id, $employeeId);
    }

    public function find(int $userId, int $employeeId, int $id)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        return $this->repository->find($companyUser->company_id, $employeeId, $id);
    }

    public function create(int $userId, int $employeeId, array $data)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        $companyId = $companyUser->company_id;

        // Validate that the employee belongs to the company
        $employee = $this->employeeRepository->getEmployeeById($companyId, $employeeId);
        if (!$employee) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => ['employee_id' => 'This employee does not belong to your company.']
            ], 422);
        }

        // Validate that the monthly salary adjustment belongs to the same company
        $adjustment = $this->monthlySalaryAdjustmentRepository->findByCompany($companyId, $data['monthly_salary_adjustment_id']);
        if (!$adjustment) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => ['monthly_salary_adjustment_id' => 'This monthly salary adjustment does not belong to your company.']
            ], 422);
        }

        return $this->repository->create($companyId, $employeeId, $data);
    }

    public function update(int $userId, int $employeeId, int $id, array $data)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        return $this->repository->update($companyUser->company_id, $employeeId, $id, $data);
    }

    public function delete(int $userId, int $employeeId, int $id)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        $this->repository->delete($companyUser->company_id, $employeeId, $id);
    }
}
