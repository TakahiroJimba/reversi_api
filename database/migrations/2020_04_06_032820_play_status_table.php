<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PlayStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('play_status', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('user_id')->unsigned()->index();
            $table->integer('game_mode_id')->unsigned()->index();
            $table->integer('wins')->unsigned();
            $table->integer('loses')->unsigned();
            $table->integer('drows')->unsigned();
            $table->integer('surrenders')->unsigned();
            $table->integer('surrenders_opponent')->unsigned();
            $table->integer('disconnect_count')->unsigned();
            $table->integer('disconnect_count_opponent')->unsigned();
            $table->bigInteger('play_time')->unsigned();
            $table->timestamps();
            $table->softDeletes();      // deleted_at
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); //外部キー参照
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
