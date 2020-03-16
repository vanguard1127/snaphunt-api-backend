<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HuntMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hunt_members', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->primary("uuid");
            $table->uuid('user_id');
            $table->string('status');
            $table->uuid('hunt_id');
            $table->timestamps();

            $table->foreign('hunt_id')
            ->references('uuid')->on('hunts')
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
        Schema::drop('hunt_members');
    }
}
