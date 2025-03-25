<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTimeBasedSalaryAdjustmentDate extends Model
{
    protected $table = 'employee_time_based_salary_adjustment_dates';

    protected $fillable = [
        'employee_time_based_salary_adjustment_id',
        'date',
    ];

    // Disable timestamps if your table doesn't have created_at/updated_at columns.
    public $timestamps = false;

    /**
     * Get the parent time-based salary adjustment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employeeTimeBasedSalaryAdjustment(): BelongsTo
    {
        return $this->belongsTo(EmployeeTimeBasedSalaryAdjustment::class);
    }
}
