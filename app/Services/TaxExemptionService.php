<?php

namespace App\Services;

use App\Repositories\TaxExemptionRepository;

class TaxExemptionService
{
    protected $repository;

    public function __construct(TaxExemptionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): array
    {
        return $this->repository->getAll()->toArray();
    }

    public function getByEmployee(int $employeeId): array
    {
        return $this->repository->getByEmployee($employeeId)->toArray();
    }

    public function find(int $id)
    {
        return $this->repository->find($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->repository->delete($id);
    }
}
