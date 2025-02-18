<?php

namespace App\Services;

use App\Repositories\MonthlySalaryAdjustmentRepository;
use App\Repositories\CompanyUserRepository;

class MonthlySalaryAdjustmentService
{
    protected $repository;
    protected $companyUserRepository;

    public function __construct(MonthlySalaryAdjustmentRepository $repository, CompanyUserRepository $companyUserRepository)
    {
        $this->repository = $repository;
        $this->companyUserRepository = $companyUserRepository;
    }

    public function getAll(int $userId)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        return $this->repository->getAll($companyUser->company_id);
    }

    public function find(int $userId, int $id)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        return $this->repository->findByCompany($companyUser->company_id, $id);
    }

    public function create(int $userId, array $data)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        $data['company_id'] = $companyUser->company_id;

        return $this->repository->create($data);
    }

    public function update(int $userId, int $id, array $data)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        return $this->repository->update($companyUser->company_id, $id, $data);
    }

    public function delete(int $userId, int $id)
    {
        $companyUser = $this->companyUserRepository->getCompanyUserByUserId($userId);
        $this->repository->delete($companyUser->company_id, $id);
    }
}
