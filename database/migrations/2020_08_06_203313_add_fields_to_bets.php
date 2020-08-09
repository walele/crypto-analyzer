<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToBets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bets', function (Blueprint $table) {
          $table->string('strategy', 200)->change();
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
        Schema::table('bets', function (Blueprint $table) {
            //
        });
    }
}
