<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(config('nutgram-admin-panel.table_names.channels', 'nutgram_channels'), function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id')->unique();
            $table->string('title');
            $table->string('username')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('invite_link')->nullable();
            $table->unsignedInteger('member_count')->default(0);
            $table->timestamps();

            $table->index('is_mandatory');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('nutgram-admin-panel.table_names.channels', 'nutgram_channels'));
    }
};
