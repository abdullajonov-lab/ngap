<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('ngap.tables.ads', 'ngap_ads'), function (Blueprint $table) {
            $table->id();
            $table->bigInteger('admin_telegram_id');
            $table->bigInteger('message_id');
            $table->bigInteger('from_chat_id');
            $table->integer('total_users')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->enum('status', ['pending', 'sending', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('admin_telegram_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('ngap.tables.ads', 'ngap_ads'));
    }
};
