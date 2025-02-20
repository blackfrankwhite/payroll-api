<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->string('type', 50)->change();
        });
    }

    public function down()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->enum('type', ['monthly', 'daily', 'hourly', 'annually'])->change();
        });
    }
};
