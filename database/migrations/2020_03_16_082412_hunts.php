<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Hunts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hunts', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->primary("uuid");
            $table->uuid('user_id');
            $table->string('title');
            $table->string('status');
            $table->timestamps();


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
        Schema::drop('hunts');
    }
}
