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
        Schema::create('provider_unavailabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_service_type_id');
            $table->foreign('provider_service_type_id')->references('id')->on('provider_service_types')->onDelete('cascade');

            $table->date('unavailable_date');
            $table->time('start_time')->nullable(); // null means full-day
            $table->time('end_time')->nullable();   // null means full-day
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
        Schema::dropIfExists('provider_unavailabilities');
    }
};
