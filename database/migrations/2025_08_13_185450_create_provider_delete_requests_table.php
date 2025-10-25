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
        Schema::create('provider_delete_requests', function (Blueprint $table) {
            $table->id();
           $table->unsignedBigInteger('provider_id');
            $table->text('reason')->nullable(); // Reason provided by the provider
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('processed_by')->nullable(); // Admin who processed the request
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable(); // Reason for rejection by admin
            $table->text('admin_notes')->nullable(); // Additional notes by admin
            $table->json('additional_data')->nullable(); // Any additional data (provider stats at time of request, etc.)
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');

            // Indexes for better query performance
            $table->index(['status', 'created_at']);
            $table->index(['provider_id', 'status']);
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_delete_requests');
    }
};
