<?php

namespace App\Services;

use App\Repositories\OneTimeAdjustmentRepository;
use App\Repositories\CompanyUserRepository;

class OneTimeAdjustmentService
{
    protected $repository;
    protected $companyUserRepository;

    public function __construct(OneTimeAdjustmentRepository $repository, CompanyUserRepository $companyUserRepository)
    {
        $this->repository = $repository;
        $this->companyUserRepository = $companyUserRepository;        
    }

    /**
     * Retrieve all one-time adjustments for a specific employee.
     */
    public function getAllByEmployee(int $employeeId)
    {
        return $this->repository->getAllByEmployee($employeeId);
    }

    /**
     * Find a one-time adjustment by its ID.
     */
    public function find(int $id)
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new one-time adjustment.
     */
    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing one-time adjustment.
     */
    public function update(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Delete a one-time adjustment.
     */
    public function delete(int $id)
    {
        return $this->repository->delete($id);
    }

    public function getAllByCompany(int $userID)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userID);

        return $this->repository->getAllByCompany($companyUser->id);
    }
}
