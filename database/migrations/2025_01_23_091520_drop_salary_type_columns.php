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
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });

        Schema::table('benefits', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });

        Schema::table('deductions', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->enum('payment_type', ['net', 'gross'])->nullable();
        });

        Schema::table('benefits', function (Blueprint $table) {
            $table->enum('payment_type', ['net', 'gross'])->nullable();
        });

        Schema::table('deductions', function (Blueprint $table) {
            $table->enum('payment_type', ['net', 'gross'])->nullable();
        });
    }
};
