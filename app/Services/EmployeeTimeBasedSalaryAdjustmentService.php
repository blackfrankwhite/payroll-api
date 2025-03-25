<?php

namespace App\Services;

use App\Repositories\EmployeeTimeBasedSalaryAdjustmentRepository;
use App\Repositories\TimeBasedSalaryAdjustmentRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\CompanyUserRepository;

class EmployeeTimeBasedSalaryAdjustmentService
{
    protected $repository;
    protected $timeBasedSalaryAdjustmentRepository;
    protected $employeeRepository;
    protected $companyUserRepository;

    public function __construct(
        EmployeeTimeBasedSalaryAdjustmentRepository $repository,
        TimeBasedSalaryAdjustmentRepository $timeBasedSalaryAdjustmentRepository,
        EmployeeRepository $employeeRepository,
        CompanyUserRepository $companyUserRepository
    ) {
        $this->repository = $repository;
        $this->timeBasedSalaryAdjustmentRepository = $timeBasedSalaryAdjustmentRepository;
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

        // Validate that the employee belongs to the company.
        $employee = $this->employeeRepository->getEmployeeById($companyId, $employeeId);
        if (!$employee) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => ['employee_id' => 'This employee does not belong to your company.']
            ], 422);
        }

        // Validate that the time-based adjustment belongs to the same company.
        $adjustment = $this->timeBasedSalaryAdjustmentRepository->findByCompany($companyId, $data['time_based_salary_adjustment_id']);
        if (!$adjustment) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => ['time_based_salary_adjustment_id' => 'This time based salary adjustment does not belong to your company.']
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
