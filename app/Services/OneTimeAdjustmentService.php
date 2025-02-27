<?php

namespace App\Services;

use App\Repositories\OneTimeAdjustmentRepository;

class OneTimeAdjustmentService
{
    protected $repository;

    public function __construct(OneTimeAdjustmentRepository $repository)
    {
        $this->repository = $repository;
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
}
