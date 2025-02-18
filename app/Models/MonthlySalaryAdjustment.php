<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlySalaryAdjustment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employeeAdjustments()
    {
        return $this->hasMany(EmployeeMonthlySalaryAdjustment::class);
    }
}
