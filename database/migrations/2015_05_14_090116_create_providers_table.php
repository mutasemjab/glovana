<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name_of_manager');
            $table->string('country_code')->default('+962');
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('email')->nullable();
            $table->string('photo_of_manager')->nullable();
            $table->text('fcm_token')->nullable();
            $table->double('balance')->default(0);
            $table->tinyInteger('activate')->default(1); // 1 yes //2 no
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('providers');
    }
};
