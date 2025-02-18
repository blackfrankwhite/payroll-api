<?php

namespace App\Repositories;

use App\Models\MonthlySalaryAdjustment;

class MonthlySalaryAdjustmentRepository
{
    public function getAll(int $companyId)
    {
        return MonthlySalaryAdjustment::where('company_id', $companyId)->get();
    }

    public function findByCompany(int $companyId, int $id)
    {
        return MonthlySalaryAdjustment::where('company_id', $companyId)
            ->where('id', $id)
            ->firstOrFail();
    }

    public function create(array $data)
    {
        return MonthlySalaryAdjustment::create($data);
    }

    public function update(int $companyId, int $id, array $data)
    {
        $adjustment = $this->findByCompany($companyId, $id);
        $adjustment->update($data);
        return $adjustment;
    }

    public function delete(int $companyId, int $id)
    {
        $adjustment = $this->findByCompany($companyId, $id);
        $adjustment->delete();
    }
}
