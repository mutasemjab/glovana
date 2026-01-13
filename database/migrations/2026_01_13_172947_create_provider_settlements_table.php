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
        Schema::create('provider_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('settlement_cycle_id');
            $table->unsignedBigInteger('provider_id');
            $table->double('total_appointments_amount')->default(0);
            $table->double('commission_amount')->default(0);
            $table->double('net_amount')->default(0); // Amount provider should receive/pay
            $table->integer('total_appointments')->default(0);
            $table->tinyInteger('payment_status')->default(1); // 1 = pending, 2 = paid
            $table->text('notes')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            
            $table->foreign('settlement_cycle_id')->references('id')->on('settlement_cycles')->onDelete('cascade');
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
        Schema::dropIfExists('provider_settlements');
    }
};
