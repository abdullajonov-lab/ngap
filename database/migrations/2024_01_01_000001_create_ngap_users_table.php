<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('ngap.tables.users', 'ngap_users'), function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id')->unique();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('language_code', 10)->default('en');
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->index('is_blocked');
            $table->index('created_at');
            $table->index('last_active_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('ngap.tables.users', 'ngap_users'));
    }
};
