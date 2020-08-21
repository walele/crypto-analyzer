<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('market');
            $table->string('payload', 2048)->nullable();
            $table->string('strategy', 200)->nullable();

            $table->boolean('active')->nullable();
            $table->boolean('success')->nullable();

            $table->string('buy_price', 50)->nullable();
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
        Schema::dropIfExists('trades');
    }
}
