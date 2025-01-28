<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeTaxExemption extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'limit_type',
        'amount',
    ];
}
