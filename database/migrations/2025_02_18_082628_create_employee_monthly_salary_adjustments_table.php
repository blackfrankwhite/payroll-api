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
        Schema::create('employee_monthly_salary_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('monthly_salary_adjustment_id')
                ->constrained('monthly_salary_adjustments', 'id')
                ->onDelete('cascade')
                ->index('emp_monthly_salary_adj_fk');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('payment_currency')->nullable(); // Fixed typo
            $table->string('calculation_currency')->nullable();
            $table->decimal('amount', 10, 2);
            $table->boolean('includes_income_tax')->default(false);
            $table->boolean('includes_employee_pension')->default(false);
            $table->boolean('includes_company_pension')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_monthly_salary_adjustments'); // Fixed table name
    }
};
