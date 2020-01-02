<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Challenge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->primary("uuid");
            $table->string('post_type');
            $table->string('owner_id');
            $table->string('description')->nullable();
            $table->integer('category');
            $table->string('privacy');
            $table->string('media');
            $table->string('status')->default(1);
            $table->boolean('is_draft')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('challenges');
    }
}
