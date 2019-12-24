<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NotificationSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->primary("uuid");
            $table->string('user_id');
            $table->boolean('stop_all')->default(false);
            $table->boolean('sponsored_alert')->default(false);
            $table->boolean('followers_alert')->default(false);
            $table->boolean('disable_commenting')->default(false);
            $table->boolean('private_account')->default(false);
            $table->boolean('save_login')->default(false);
            $table->boolean('sync_contacts')->default(false);
            $table->boolean('auto_promote')->default(false);
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
        Schema::drop('user_settings');
    }
}
