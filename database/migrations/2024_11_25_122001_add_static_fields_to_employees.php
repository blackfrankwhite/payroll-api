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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('id_number')->nullable();
            $table->string('surname');
            $table->string('gender');
            $table->date('birth_date')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('residency')->nullable();
            $table->string('address')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('pension')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('id_number');
            $table->dropColumn('surname');
            $table->dropColumn('gender');
            $table->dropColumn('birth_date');
            $table->dropColumn('bank_account');
            $table->dropColumn('residency');
            $table->dropColumn('address');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('pension');
        });
    }
};
