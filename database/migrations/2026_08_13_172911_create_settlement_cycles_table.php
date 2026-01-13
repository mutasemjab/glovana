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
         Schema::create('settlement_cycles', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->double('total_amount')->default(0);
            $table->double('total_commission')->default(0);
            $table->tinyInteger('status')->default(1); // 1 = pending, 2 = completed
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['start_date', 'end_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settlement_cycles');
    }
};
