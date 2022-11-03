<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelegramBotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telegram_bots', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id');
            $table->string('username');
            $table->string('first_name');
            $table->boolean('can_join_groups')->default(true);
            $table->boolean('can_read_all_group_messages')->default(false);
            $table->boolean('supports_inline_queries')->default(false);
            $table->timestamps();
        });

        Schema::create('telegram_chat_telegram_bot_assigment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_chat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('telegram_bot_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('status')->default(0);
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
        Schema::dropIfExists('telegram_chat_telegram_bot_assigment');
        Schema::dropIfExists('telegram_bots');
    }
}
