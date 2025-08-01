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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->double('value')->default(1);
            $table->timestamps();
        });
        DB::table('settings')->insert([
            ['key' => "number_of_points_to_convert_to_money", 'value' => 1],
            ['key' => "one_point_equal_money", 'value' => 1],
            ['key' => "commission_of_admin", 'value' => 1.5],
            ['key' => "calculate_delivery_fee_depend_on_the_place_or_distance", 'value' => 1], // 1 place // 2 distance
            ['key' => "start_price", 'value' => 0.25],
            ['key' => "price_per_km", 'value' => 0.15],
            ['key' => "minimum_to_notify_me_for_quantity_products", 'value' => 2],
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
