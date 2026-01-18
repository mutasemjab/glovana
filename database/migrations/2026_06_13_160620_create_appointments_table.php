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
            $table->string('payment_type'); // cash //visa //wallet
            $table->tinyInteger('payment_status')->default(2); // 1 Paid   // 2 Unpaid
            $table->text('reason_of_cancel')->nullable();
            $table->dateTime('date');
            $table->text('note')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->double('fine_amount')->default(0);
            $table->tinyInteger('fine_applied')->default(2); // 1 yes // 2 no

            $table->double('original_total_price')->nullable();
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->double('discount_percentage')->default(0);
            $table->double('discount_amount')->default(0);
            $table->tinyInteger('has_discount')->default(2); // 1 = yes, 2 = no
            $table->tinyInteger('cancel_rating')->default(2); // 1 = yes, 2 = no

            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->double('points_discount_amount')->default(0);
            $table->tinyInteger('points_awarded')->default(2)->comment('1=yes, 2=no');
            $table->tinyInteger('rating_points_awarded')->default(2)->comment('1=yes, 2=no');

            // Add foreign key for discount
            $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('set null');

            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');

            $table->unsignedBigInteger('settlement_cycle_id')->nullable();
            $table->tinyInteger('settlement_status')->default(1); // 1 = pending, 2 = settled

            $table->foreign('settlement_cycle_id')->references('id')->on('settlement_cycles')->onDelete('set null');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('address_id')->nullable();
            $table->foreign('address_id')->references('id')->on('user_addresses')->onDelete('cascade');
            $table->unsignedBigInteger('provider_type_id');
            $table->foreign('provider_type_id')->references('id')->on('provider_types')->onDelete('cascade');
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['discount_id', 'has_discount']);
            $table->index(['provider_type_id', 'has_discount']);
            $table->index(['settlement_cycle_id', 'settlement_status']);
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
