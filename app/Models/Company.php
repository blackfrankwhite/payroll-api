<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use softDeletes;

    protected $fillable = [
        'name',
        'email',
        'logo',
        'address',
        'mobile',
        'identification_code',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
