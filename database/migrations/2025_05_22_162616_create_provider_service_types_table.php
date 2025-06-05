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
        Schema::create('provider_service_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('type_id');
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');

            $table->tinyInteger('activate')->default(1); // 1 active // 2 not active 
            $table->string('name'); 
            $table->text('description');
            $table->double('lat');
            $table->double('lng');
            $table->text('address')->nullable();
            $table->double('price_per_hour')->default(0); 
            $table->tinyInteger('status')->default(1); // 1 on //2 off
            $table->tinyInteger('is_vip')->default(2); // 1 yes //2 no
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
        Schema::dropIfExists('provider_service_types');
    }
};
