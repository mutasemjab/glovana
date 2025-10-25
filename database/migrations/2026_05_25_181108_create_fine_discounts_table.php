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
        Schema::create('fine_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            
            // Related entities
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            
            // Fine/Discount details
            $table->tinyInteger('category')->default(1); // 1 automatic (late cancellation) // 2 manual
            $table->double('amount');
            $table->double('percentage')->nullable(); // For percentage-based fines
            $table->double('original_amount')->nullable(); // Original appointment amount for reference
            
            // Status and processing
            $table->tinyInteger('status')->default(1); // 1 pending // 2 applied // 3 reversed // 4 failed
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->dateTime('applied_at')->nullable();
            $table->dateTime('due_date')->nullable(); // For delayed application
            
            // Reference to wallet transaction
            $table->unsignedBigInteger('wallet_transaction_id')->nullable();
            $table->foreign('wallet_transaction_id')->references('id')->on('wallet_transactions')->onDelete('set null');
            
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
        Schema::dropIfExists('fine_discounts');
    }
};
