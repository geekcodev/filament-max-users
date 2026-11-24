<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Имитация таблицы хост-приложения, которая ссылается на max_users внешним
 * ключом: удаление пользователя из реестра должно откатываться, а не ломать
 * ссылочную целостность.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('host_profiles', function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('max_user_id')->nullable();
            $table->foreign('max_user_id')->references('user_id')->on('max_users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('host_profiles');
    }
};
