<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'type' => $this->type,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'includes_income_tax' => $this->includes_income_tax,
            'includes_employee_pension' => $this->includes_employee_pension,
            'includes_company_pension' => $this->includes_company_pension,
            'daily_salary_calculation_base' => $this->daily_salary_calculation_base,
            'daily_working_hours' => $this->daily_working_hours,
            'non_working_days' => $this->nonWorkingDays->pluck('day')->toArray(),
            'non_working_custom_dates' => $this->nonWorkingCustomDates->pluck('custom_date')->toArray(),
        ];
    }
}
