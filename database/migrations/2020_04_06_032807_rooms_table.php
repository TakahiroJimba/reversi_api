<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->string('name');
            $table->string('password');
            $table->integer('owner_user_id')->unsigned()->index();
            $table->integer('challenger_user_id')->unsigned();
            $table->integer('board_size');
            $table->timestamps();
            $table->softDeletes();      // deleted_at
            $table->foreign('owner_user_id')->references('id')->on('users')->onDelete('cascade'); //外部キー参照
            $table->foreign('challenger_user_id')->references('id')->on('users')->onDelete('cascade');
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
