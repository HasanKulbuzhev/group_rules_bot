<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveTelegramUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->bigInteger('telegram_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->bigInteger('telegram_id')->change();
        });
    }
}
