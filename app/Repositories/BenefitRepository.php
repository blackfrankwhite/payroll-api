<?php

namespace App\Repositories;

use App\Models\Benefit;
use Carbon\Carbon;

class BenefitRepository
{
    public function create(array $data)
    {
        return Benefit::create($data);
    }

    public function getByEmployee(int $employeeID)
    {
        return Benefit::where('employee_id', $employeeID)->get();
    }

    public function getById(int $employeeID, int $benefitID)
    {
        return Benefit::where('employee_id', $employeeID)
            ->where('id', $benefitID)
            ->firstOrFail();
    }

    public function update(int $employeeID, int $benefitID, array $data)
    {
        $benefit = $this->getById($employeeID, $benefitID);
        $benefit->update($data);
        return $benefit;
    }

    public function delete(int $employeeID, int $benefitID)
    {
        $benefit = $this->getById($employeeID, $benefitID);
        $benefit->delete();
    }

    public function getBenefitCoverage($startDate, $endDate, $employeeId)
    {
        $benefits = Benefit::where('employee_id', $employeeId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->where(function ($subQuery) use ($startDate) {
                          $subQuery->where('end_date', '>=', $startDate)
                                   ->orWhereNull('end_date');
                      });
            })
            ->orderBy('start_date')
            ->get();

        $intervals = [];
        foreach ($benefits as $benefit) {
            $benefitStart = Carbon::parse($benefit->start_date);
            $benefitEnd = $benefit->end_date
                ? Carbon::parse($benefit->end_date)
                : Carbon::parse($endDate);

            $clampedStart = $benefitStart->lt($startDate) ? Carbon::parse($startDate) : $benefitStart;
            $clampedEnd = $benefitEnd->gt($endDate) ? Carbon::parse($endDate) : $benefitEnd;

            if ($clampedStart->lte($clampedEnd)) {
                $intervals[] = [
                    'start' => $clampedStart,
                    'end' => $clampedEnd,
                    'amount' => $benefit->amount,
                    'includes_income_tax' => $benefit->includes_income_tax,
                    'includes_employee_pension' => $benefit->includes_employee_pension,
                    'includes_company_pension' => $benefit->includes_company_pension,
                ];
            }
        }

        return $intervals;
    }
}
