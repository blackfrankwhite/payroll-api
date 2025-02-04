<?php

namespace App\Repositories;

use App\Models\IncentiveBonus;
use Carbon\Carbon;

class IncentiveBonusRepository
{
    public function create(array $data)
    {
        return IncentiveBonus::create($data);
    }

    public function getByEmployee(int $employeeID)
    {
        return IncentiveBonus::where('employee_id', $employeeID)->get();
    }

    public function getById(int $employeeID, int $incentiveBonusID)
    {
        return IncentiveBonus::where('employee_id', $employeeID)
            ->where('id', $incentiveBonusID)
            ->firstOrFail();
    }

    public function update(int $employeeID, int $incentiveBonusID, array $data)
    {
        $incentiveBonus = $this->getById($employeeID, $incentiveBonusID);
        $incentiveBonus->update($data);
        return $incentiveBonus;
    }

    public function delete(int $employeeID, int $incentiveBonusID)
    {
        $incentiveBonus = $this->getById($employeeID, $incentiveBonusID);
        $incentiveBonus->delete();
    }

    public function getIncentiveBonusCoverage($startDate, $endDate, $employeeId)
    {
        $bonuses = IncentiveBonus::where('employee_id', $employeeId)
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
        foreach ($bonuses as $bonus) {
            $bonusStart = Carbon::parse($bonus->start_date);
            $bonusEnd = $bonus->end_date
                ? Carbon::parse($bonus->end_date)
                : Carbon::parse($endDate);

            $clampedStart = $bonusStart->lt($startDate) ? Carbon::parse($startDate) : $bonusStart;
            $clampedEnd = $bonusEnd->gt($endDate) ? Carbon::parse($endDate) : $bonusEnd;

            if ($clampedStart->lte($clampedEnd)) {
                $intervals[] = [
                    'start' => $clampedStart,
                    'end' => $clampedEnd,
                    'amount' => $bonus->amount,
                    'includes_income_tax' => $bonus->includes_income_tax,
                    'includes_employee_pension' => $bonus->includes_employee_pension,
                    'includes_company_pension' => $bonus->includes_company_pension,
                ];
            }
        }

        return $intervals;
    }
}
