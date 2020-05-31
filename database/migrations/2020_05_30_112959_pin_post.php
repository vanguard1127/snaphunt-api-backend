<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PinPost extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_post', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->primary("uuid");
            $table->uuid('user_id');
            $table->uuid('post_id');
            $table->timestamps();

            $table->foreign('post_id')
            ->references('uuid')->on('challenges')
            ->onDelete('cascade');

            $table->foreign('user_id')
            ->references('uuid')->on('users')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pin_post');
    }
}
