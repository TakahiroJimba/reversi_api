<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TempUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_users', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->string('mail_address')->unique()->index();
            $table->string('name')->unique();
            $table->string('password');
            $table->string('pass_phrase');
            $table->timestamps();
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
