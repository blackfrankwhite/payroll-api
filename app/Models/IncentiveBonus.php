<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncentiveBonus extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'percent',
        'payment_currency',
        'calculation_currency',
        'includes_income_tax',
        'includes_employee_pension',
        'includes_company_pension',
        'current_benefit',
        'start_date',
        'end_date',
    ];
}
