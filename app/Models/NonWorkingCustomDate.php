<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NonWorkingCustomDate extends Model
{
    protected $fillable = [
        'salary_id', 
        'custom_date'
    ];

    protected $visible = [
        'custom_date'
    ];
}
