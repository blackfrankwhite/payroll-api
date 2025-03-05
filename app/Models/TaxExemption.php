<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxExemption extends Model
{
    protected $fillable = [
        'employee_id',
        'name',
        'start_date',
        'end_date',
        'renewable',
        'amount',
        'percent',
        'constant',
        'balance_amount',
        'balance_date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
