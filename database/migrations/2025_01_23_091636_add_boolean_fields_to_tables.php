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
        Schema::table('tables', function (Blueprint $table) {
            Schema::table('salaries', function (Blueprint $table) {
                $table->boolean('includes_income_tax')->default(false);
                $table->boolean('includes_employee_pension')->default(false);
                $table->boolean('includes_company_pension')->default(false);
            });
    
            Schema::table('benefits', function (Blueprint $table) {
                $table->boolean('includes_income_tax')->default(false);
                $table->boolean('includes_employee_pension')->default(false);
                $table->boolean('includes_company_pension')->default(false);
            });
    
            Schema::table('deductions', function (Blueprint $table) {
                $table->boolean('includes_income_tax')->default(false);
                $table->boolean('includes_employee_pension')->default(false);
                $table->boolean('includes_company_pension')->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            Schema::table('salaries', function (Blueprint $table) {
                $table->dropColumn('includes_income_tax');
                $table->dropColumn('includes_employee_pension');
                $table->dropColumn('includes_company_pension');
            });
    
            Schema::table('benefits', function (Blueprint $table) {
                $table->dropColumn('includes_income_tax');
                $table->dropColumn('includes_employee_pension');
                $table->dropColumn('includes_company_pension');
            });
    
            Schema::table('deductions', function (Blueprint $table) {
                $table->dropColumn('includes_income_tax');
                $table->dropColumn('includes_employee_pension');
                $table->dropColumn('includes_company_pension');
            });
        });
    }
};
