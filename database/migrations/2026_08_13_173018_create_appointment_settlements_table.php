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
        Schema::create('appointment_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('settlement_cycle_id');
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('provider_id');
            $table->double('appointment_amount');
            $table->double('commission_amount');
            $table->double('provider_amount');
            $table->string('payment_type'); // cash, visa, wallet
            $table->timestamps();
            
            $table->foreign('settlement_cycle_id')->references('id')->on('settlement_cycles')->onDelete('cascade');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            
            $table->index(['settlement_cycle_id', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointment_settlements');
    }
};
