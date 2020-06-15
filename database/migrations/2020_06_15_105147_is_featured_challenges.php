<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IsFeaturedChallenges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenges', function($table) {
            $table->boolean('is_featured')->default(false);
            $table->integer('featured_duration')->nullable();
            $table->timestamp('featured_ends')->nullable();
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
            $table->dropColumn('is_featured');
            $table->dropColumn('featured_duration');
            $table->dropColumn('featured_ends');
        });
    }
}
