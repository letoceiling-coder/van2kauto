<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('chat_token', 48)->nullable()->unique();
            $table->timestamp('last_client_message_at')->nullable();
        });

        Schema::create('lead_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->string('sender', 16); // client | manager | assistant
            $table->string('author')->nullable();
            $table->text('text');
            $table->timestamps();
            $table->index(['lead_id', 'id']);
        });

        // Какие сообщения в Telegram относятся к заявке — чтобы ответ менеджера реплаем попал в нужный чат.
        Schema::create('lead_tg_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->bigInteger('chat_id');
            $table->bigInteger('message_id');
            $table->timestamps();
            $table->unique(['chat_id', 'message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_tg_messages');
        Schema::dropIfExists('lead_messages');
        Schema::table('leads', function (Blueprint $table) {
            $table->dropUnique(['chat_token']);
            $table->dropColumn(['chat_token', 'last_client_message_at']);
        });
    }
};
