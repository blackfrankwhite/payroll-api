<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeTimeBasedSalaryAdjustmentsTable extends Migration
{
    public function up()
    {
        Schema::create('employee_time_based_salary_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('time_based_salary_adjustment_id');
            $table->bigInteger('amount');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('time_based_salary_adjustment_id', 'emp_timeadj_fk')
                ->references('id')
                ->on('time_based_salary_adjustments')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_time_based_salary_adjustments');
    }
}
