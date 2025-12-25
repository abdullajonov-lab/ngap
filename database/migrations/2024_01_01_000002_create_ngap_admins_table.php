<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('ngap.tables.admins', 'ngap_admins'), function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id')->unique();
            $table->string('name');
            $table->boolean('is_main')->default(false);
            $table->boolean('can_send_ads')->default(false);
            $table->boolean('can_add_admin')->default(false);
            $table->boolean('can_del_admin')->default(false);
            $table->boolean('can_add_channel')->default(false);
            $table->boolean('can_del_channel')->default(false);
            $table->boolean('can_view_stats')->default(true);
            $table->timestamps();

            $table->index('is_main');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('ngap.tables.admins', 'ngap_admins'));
    }
};
