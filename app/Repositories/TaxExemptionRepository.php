<?php

namespace App\Repositories;

use App\Models\TaxExemption;

class TaxExemptionRepository
{
    public function getAll()
    {
        return TaxExemption::all();
    }

    public function find(int $id)
    {
        return TaxExemption::findOrFail($id);
    }

    public function getByEmployee(int $employeeId)
    {
        return TaxExemption::where('employee_id', $employeeId)->get();
    }

    public function create(array $data)
    {
        return TaxExemption::create($data);
    }

    public function update(int $id, array $data)
    {
        $exemption = $this->find($id);
        $exemption->update($data);
        return $exemption;
    }

    public function delete(int $id)
    {
        $exemption = $this->find($id);
        return $exemption->delete();
    }
}
