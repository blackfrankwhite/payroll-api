<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_time_based_salary_adjustment_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_time_based_salary_adjustment_id');
            $table->foreign('employee_time_based_salary_adjustment_id', 'etbsai_dates')
                ->references('id')
                ->on('employee_time_based_salary_adjustments')
                ->onDelete('cascade');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_time_based_salary_adjustment_dates');
    }
};
