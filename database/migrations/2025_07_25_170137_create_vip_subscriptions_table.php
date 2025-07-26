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
        Schema::create('vip_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_type_id');
            $table->foreign('provider_type_id')->references('id')->on('provider_types')->onDelete('cascade');
            
            $table->date('start_date');
            $table->date('end_date');
            $table->double('amount_paid');
            $table->text('notes')->nullable();
            
            $table->tinyInteger('status')->default(1); // 1 active // 2 inactive // 3 expired
            $table->tinyInteger('payment_status')->default(2); // 1 paid // 2 unpaid
            $table->string('payment_method')->nullable(); // cash, visa, wallet, etc.
            
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
        Schema::dropIfExists('vip_subscriptions');
    }
};
