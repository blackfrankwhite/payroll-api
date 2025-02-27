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
        Schema::create('one_time_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->string('type')->nullable();
            $table->float('amount', 10, 2);
            $table->string('calculation_currency', 3)->default('GEL');
            $table->boolean('includes_income_tax')->default(false);
            $table->boolean('includes_employee_pension')->default(false);
            $table->date('date');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_time_adjustments');
    }
};
