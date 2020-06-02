<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PaymentMethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_method', function (Blueprint $table) {
            $table->uuid("uuid");
            $table->primary("uuid");
            $table->uuid('user_id');
            $table->jsonb('stripe_object');
            $table->string('card_token');
            $table->string('status')->default("active");
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
        Schema::drop('payment_method');
    }
}
