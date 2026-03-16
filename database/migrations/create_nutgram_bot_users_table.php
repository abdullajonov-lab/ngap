<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(config('nutgram-admin-panel.table_names.bot_users', 'nutgram_bot_users'), function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('language_code', 10)->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('nutgram-admin-panel.table_names.bot_users', 'nutgram_bot_users'));
    }
};
