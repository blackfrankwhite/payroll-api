<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salary extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'type',
        'amount',
        'currency',
        'start_date',
        'end_date',
        'includes_income_tax',
        'includes_employee_pension',
        'includes_company_pension',
        'daily_salary_calculation_base',
        'daily_working_hours'
    ];

    protected $casts = [
        'includes_income_tax' => 'boolean',
        'includes_employee_pension' => 'boolean',
        'includes_company_pension' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function nonWorkingDays()
    {
        return $this->hasMany(NonWorkingDay::class);
    }

    public function nonWorkingCustomDates()
    {
        return $this->hasMany(NonWorkingCustomDate::class);
    }
}


