<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FeaturedHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured_history', function (Blueprint $table) {

            $table->uuid("uuid");
            $table->primary("uuid");
            $table->uuid('user_id');
            $table->uuid('ch_id');
            $table->integer('duration');
            $table->timestamp('featured_ends');

            $table->double('amount')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default("unpaid");
            $table->string('charge_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
            ->references('uuid')->on('users')
            ->onDelete('cascade');

            $table->foreign('ch_id')
            ->references('uuid')->on('challenges')
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
        Schema::drop('featured_history');
    }
}
