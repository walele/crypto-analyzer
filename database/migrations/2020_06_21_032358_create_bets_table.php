<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('end_at')->nullable();
            $table->string('market');
            $table->string('strategy')->nullable();
            $table->string('strategy_key')->nullable();
            $table->string('conditions', 2048)->nullable();
            $table->string('features', 2048)->nullable();
            $table->string('ml_status')->nullable();

            $table->boolean('active')->nullable();
            $table->boolean('success')->nullable();
            $table->boolean('traded')->nullable();

            $table->string('buy_price', 50)->nullable();
            $table->string('sell_price', 50)->nullable();
            $table->string('stop_price', 50)->nullable();

            $table->string('final_min_price', 50)->nullable();
            $table->string('final_max_price', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bets');
    }
}
