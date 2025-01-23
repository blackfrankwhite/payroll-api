<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->renameColumn('currency', 'payment_currency');
            $table->string('calculation_currency', 3)->after('payment_currency');
        });

        Schema::table('benefits', function (Blueprint $table) {
            $table->renameColumn('currency', 'payment_currency');
            $table->string('calculation_currency', 3)->after('payment_currency');
        });

        Schema::table('deductions', function (Blueprint $table) {
            $table->renameColumn('currency', 'payment_currency');
            $table->string('calculation_currency', 3)->after('payment_currency');
        });
    }

    public function down()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->renameColumn('payment_currency', 'currency');
            $table->dropColumn('calculation_currency');
        });

        Schema::table('benefits', function (Blueprint $table) {
            $table->renameColumn('payment_currency', 'currency');
            $table->dropColumn('calculation_currency');
        });

        Schema::table('deductions', function (Blueprint $table) {
            $table->renameColumn('payment_currency', 'currency');
            $table->dropColumn('calculation_currency');
        });
    }
};
