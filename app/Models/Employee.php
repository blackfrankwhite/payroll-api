<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use softDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'position',
        'name',
        'email',
        'phone',
    ];
}