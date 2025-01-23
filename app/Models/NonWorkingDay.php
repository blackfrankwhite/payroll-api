<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NonWorkingDay extends Model
{
    protected $fillable = [
        'salary_id', 
        'day'
    ];

    protected $visible = [
        'day'
    ];
}
