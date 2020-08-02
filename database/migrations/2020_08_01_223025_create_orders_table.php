<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('market');
            $table->string('type');
            $table->string('price')->nullable();
            $table->string('real_price')->nullable();
            $table->string('quantity')->nullable();
            $table->string('real_quantity')->nullable();
            $table->string('btc_amount')->nullable();
            $table->string('real_btc_amount')->nullable();
            $table->boolean('active')->nullable();
            $table->boolean('success')->nullable();

            $table->string('payload', 2048)->nullable();
            $table->string('binance_payload', 2048)->nullable();
            $table->string('log', 2048)->nullable();
            $table->string('binance_id')->nullable();
            $table->string('wallet_btc')->nullable();
            $table->integer('trade_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
