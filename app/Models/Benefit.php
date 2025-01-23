<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Benefit extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'employee_id',
        'type',
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
}
