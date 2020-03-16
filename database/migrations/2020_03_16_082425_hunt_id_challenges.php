<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HuntIdChallenges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenges', function($table) {
            $table->uuid('hunt_id')->nullable();

            $table->foreign('hunt_id')
                ->references('uuid')->on('hunts')
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
        Schema::table('challenges', function($table) {
            $table->dropColumn('hunt_id');
        });
    }
}
