<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->integer('user_id')->unsigned()->index();
            $table->integer('opponent_user_id')->unsigned()->index();
            $table->integer('board_size')->index();
            $table->integer('my_stone_num')->index();
            $table->integer('result_id')->unsigned()->index();
            $table->boolean('is_first');
            $table->integer('game_total_time')->unsigned();
            $table->integer('your_game_time')->unsigned();
            $table->timestamps();
            $table->softDeletes();      // deleted_at
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); //外部キー参照
            $table->foreign('opponent_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('result_id')->references('id')->on('results')->onDelete('cascade');
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
