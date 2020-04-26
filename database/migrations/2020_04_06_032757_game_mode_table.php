<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GameModeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_mode', function (Blueprint $table) {
            $table->integer('id')->unsigned()->unique()->index();
            $table->string('name');
            $table->string('name_en');
            //$table->timestamps();
            $table->softDeletes();      // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
