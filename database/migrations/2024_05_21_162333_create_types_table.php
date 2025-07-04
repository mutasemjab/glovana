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
        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('photo')->nullable();
            $table->enum('booking_type', ['hourly', 'service'])->default('hourly');
            $table->tinyInteger('have_delivery')->default(1); // 1 yes //2 no
            $table->integer('minimum_order')->default(1); 
            $table->timestamps();
        });

          DB::table('types')->insert([
            ['name_en'=>'Saloon','name_ar'=>'صالونات','booking_type'=>'service'],
            ['name_en'=>'Babysitter','name_ar'=>'جليسة أطفال','booking_type'=>'hourly']
          ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('types');
    }
};
