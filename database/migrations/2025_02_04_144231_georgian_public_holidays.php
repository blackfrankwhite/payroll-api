<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('georgian_public_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('full_date')->nullable()->comment('For floating holidays like Easter, full YYYY-MM-DD date');
            $table->string('month_day', 5)->nullable()->comment('For fixed holidays like Christmas (format MM-DD)');
            $table->string('name')->comment('Name of the holiday');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('georgian_public_holidays');
    }
};
