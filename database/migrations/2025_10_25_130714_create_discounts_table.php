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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_type_id');
            $table->string('name'); // Discount name for admin reference
            $table->text('description')->nullable(); // Discount description
            $table->decimal('percentage', 5, 2); // Discount percentage (0.00 - 100.00)
            $table->date('start_date'); // Discount start date
            $table->date('end_date'); // Discount end date
            $table->enum('discount_type', ['hourly', 'service']); // What type of pricing this discount applies to
            $table->tinyInteger('is_active')->default(1); // 1 = active, 0 = inactive
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('provider_type_id')->references('id')->on('provider_types')->onDelete('cascade');
            $table->index(['provider_type_id', 'is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discounts');
    }
};
