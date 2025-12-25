<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('ngap.tables.channels', 'ngap_channels'), function (Blueprint $table) {
            $table->id();
            $table->bigInteger('channel_id')->unique();
            $table->string('username')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('ngap.tables.channels', 'ngap_channels'));
    }
};
