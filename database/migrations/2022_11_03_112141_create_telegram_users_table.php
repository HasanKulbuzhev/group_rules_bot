<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelegramUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telegram_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id');
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->timestamps();
        });

        Schema::create('telegram_chat_telegram_user_assigment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_chat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('telegram_user_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('status')->default(0);
            $table->timestamps();
        });

        Schema::table('telegram_bots', function (Blueprint $table) {
            $table->foreignId('telegram_user_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telegram_bots', function (Blueprint $table) {
            $table->dropForeign(['telegram_user_id']);
            $table->dropColumn('telegram_user_id');
        });
        Schema::dropIfExists('telegram_chat_telegram_user_assigment');
        Schema::dropIfExists('telegram_users');
    }
}
