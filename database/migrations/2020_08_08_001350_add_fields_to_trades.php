<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToTrades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trades', function (Blueprint $table) {
          $table->string('strategy', 200)->nullable();
          $table->string('sell_price', 50)->nullable();
          $table->string('stop_price', 50)->nullable();
          $table->string('final_min_price', 50)->nullable();
          $table->string('final_max_price', 50)->nullable();
      //  $table->foreignId('buy_order_id')->references('id')->on('orders')->nullable();
      //  $table->foreignId('sell_order_id')->references('id')->on('orders')->nullable();
          $table->bigInteger('buy_order_id')->nullable();
          $table->bigInteger('sell_order_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trades', function (Blueprint $table) {
            //
        });
    }
}
