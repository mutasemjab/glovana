<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('fine_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'late_cancellation_hours', 'fine_percentage'
            $table->double('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

          DB::table('fine_settings')->insert([
            ['key' => "late_cancellation_hours", 'value' => 24],
            ['key' => "fine_percentage", 'value' => 10],
            ['key' => "provider_cancellation_hours", 'value' => 2],
            ['key' => "provider_fine_percentage", 'value' => 15], 
            ['key' => "auto_apply_fines", 'value' => 1],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fine_settings');
    }
};
