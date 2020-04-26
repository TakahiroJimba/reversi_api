<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->integer('user_id')->unsigned()->index();
            $table->integer('receive_user_id')->unsigned()->index();
            $table->string('message');
            $table->timestamps();
            $table->softDeletes();      // deleted_at
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); //外部キー参照
            // $table->foreign('receive_user_id')->references('id')->on('users')->onDelete('cascade');
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
