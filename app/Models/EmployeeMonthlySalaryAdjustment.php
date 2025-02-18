<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeMonthlySalaryAdjustment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'monthly_salary_adjustment_id',
        'start_date',
        'end_date',
        'payment_currency',
        'calculation_currency',
        'amount',
        'includes_income_tax',
        'includes_employee_pension',
        'includes_company_pension',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function monthlySalaryAdjustment()
    {
        return $this->belongsTo(MonthlySalaryAdjustment::class);
    }
}
