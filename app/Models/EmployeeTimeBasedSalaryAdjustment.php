<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTimeBasedSalaryAdjustment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'time_based_salary_adjustment_id',
        'amount'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function timeBasedSalaryAdjustment()
    {
        return $this->belongsTo(TimeBasedSalaryAdjustment::class);
    }

    public function dates()
    {
        return $this->hasMany(EmployeeTimeBasedSalaryAdjustmentDate::class);
    }    
}
