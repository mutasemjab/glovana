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
        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('service_id');
            $table->integer('customer_count')->default(1);
            $table->decimal('service_price', 10, 2);
            $table->decimal('total_price', 10, 2); // service_price * customer_count
            $table->integer('person_number')->nullable();

            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
    
            $table->decimal('original_service_price', 10, 2)->nullable();
            $table->double('service_discount_percentage')->default(0);
            $table->decimal('service_discount_amount', 10, 2)->default(0);
            $table->tinyInteger('has_service_discount')->default(2); // 1 = yes, 2 = no
            
            // Add index for performance
            $table->index(['appointment_id', 'has_service_discount']);
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
        Schema::dropIfExists('appointment_services');
    }
};
