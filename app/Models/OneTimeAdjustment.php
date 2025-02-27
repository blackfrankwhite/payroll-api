<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OneTimeAdjustment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'type',
        'amount',
        'calculation_currency',
        'includes_income_tax',
        'includes_employee_pension',
        'date',
        'description',
    ];

    protected $casts = [
        'includes_income_tax' => 'boolean',
        'includes_employee_pension' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
