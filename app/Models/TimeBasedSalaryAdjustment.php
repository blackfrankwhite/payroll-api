<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeBasedSalaryAdjustment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'percent'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employeeAdjustments()
    {
        return $this->hasMany(EmployeeTimeBasedSalaryAdjustment::class);
    }
}
