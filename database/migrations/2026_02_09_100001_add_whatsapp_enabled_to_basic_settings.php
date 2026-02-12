<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('basic_settings', 'whatsapp_enabled')) {
                $table->unsignedTinyInteger('whatsapp_enabled')->default(1)->after('whatsapp_webhook_verify_token')->comment('1=enabled, 0=disabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (Schema::hasColumn('basic_settings', 'whatsapp_enabled')) {
                $table->dropColumn('whatsapp_enabled');
            }
        });
    }
};
