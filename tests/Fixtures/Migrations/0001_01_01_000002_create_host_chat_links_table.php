<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Имитация таблицы хост-приложения, которая ссылается на max_chats внешним
 * ключом: удаление чата из реестра должно откатываться, а не ломать
 * ссылочную целостность.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('host_chat_links', function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('max_chat_id')->nullable();
            $table->foreign('max_chat_id')->references('chat_id')->on('max_chats');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('host_chat_links');
    }
};
