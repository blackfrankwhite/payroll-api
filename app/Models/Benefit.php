<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Benefit extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'payment_type',
        'currency',
        'amount',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
