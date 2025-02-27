<?php

namespace App\Repositories;

use App\Models\OneTimeAdjustment;

class OneTimeAdjustmentRepository
{
    /**
     * Get all one-time adjustments for a given employee.
     */
    public function getAllByEmployee(int $employeeId)
    {
        return OneTimeAdjustment::where('employee_id', $employeeId)->get();
    }

    /**
     * Find a specific one-time adjustment by ID.
     */
    public function find(int $id)
    {
        return OneTimeAdjustment::findOrFail($id);
    }

    /**
     * Create a new one-time adjustment.
     */
    public function create(array $data)
    {
        return OneTimeAdjustment::create($data);
    }

    /**
     * Update an existing one-time adjustment.
     */
    public function update(int $id, array $data)
    {
        $adjustment = $this->find($id);
        $adjustment->update($data);
        return $adjustment;
    }

    /**
     * Delete a one-time adjustment.
     */
    public function delete(int $id)
    {
        $adjustment = $this->find($id);
        return $adjustment->delete();
    }
}
