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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country_code')->default('+962');
            $table->string('phone')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('photo')->nullable();
            $table->text('fcm_token')->nullable();
            $table->double('balance')->default(0);
            $table->integer('total_points')->default(0);
            $table->text('referral_code')->nullable();
            $table->tinyInteger('activate')->default(1); // 1 yes //2 no
            $table->unsignedBigInteger('user_id')->nullable(); 

            // for login in social google and apple 
            $table->text('google_id')->nullable();
            $table->text('apple_id')->nullable();
            $table->text('access_token')->nullable();
            $table->tinyInteger('type')->default(3); // 1 google //2 apple // 3 login by phone

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
        Schema::dropIfExists('users');
    }
};
