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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->nullable();
            $table->tinyInteger('appointment_status')->default(1);  // 1 Pending //2 Accepted //3 OnTheWay // 4 Delivered // 5 Canceled  // 6 start work // 7 arrived user to provider
            $table->double('delivery_fee');
            $table->double('total_prices');
            $table->double('total_discounts');
            $table->double('coupon_discount')->nullable();
            $table->string('payment_type');// cash //visa //wallet
            $table->tinyInteger('payment_status')->default(2); // 1 Paid   // 2 Unpaid
            $table->text('reason_of_cancel')->nullable(); 
            $table->dateTime('date');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('address_id')->nullable();
            $table->foreign('address_id')->references('id')->on('user_addresses')->onDelete('cascade');
            $table->unsignedBigInteger('provider_type_id');
            $table->foreign('provider_type_id')->references('id')->on('provider_types')->onDelete('cascade');
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
        Schema::dropIfExists('appointments');
    }
};
