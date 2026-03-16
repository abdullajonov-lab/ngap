<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(config('nutgram-admin-panel.table_names.broadcasts', 'nutgram_broadcasts'), function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->string('parse_mode')->nullable()->default('HTML');
            $table->string('status')->default('pending');
            $table->unsignedInteger('total_users')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('blocked_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('nutgram-admin-panel.table_names.broadcasts', 'nutgram_broadcasts'));
    }
};
