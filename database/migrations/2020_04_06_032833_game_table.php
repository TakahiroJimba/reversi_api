<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('game_mode_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->integer('challenger_user_id')->unsigned()->index();
            $table->integer('board_size')->unsigned();
            $table->string('cells');
            $table->integer('first_user')->unsigned();
            $table->boolean('turn_now')->nullable();
            $table->integer('turn_priority')->unsigned();
            $table->integer('user_play_time')->unsigned()->default(0);;
            $table->integer('challenger_play_time')->unsigned()->default(0);;
            $table->timestamps();
            $table->softDeletes();      // deleted_at
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); //外部キー参照
            // $table->foreign('challenger_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('game_mode_id')->references('id')->on('game_mode')->onDelete('cascade');
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
