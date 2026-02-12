<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('basic_settings', 'whatsapp_webhook_verify_token')) {
                $table->string('whatsapp_webhook_verify_token', 255)->nullable()->after('theme_version');
            }
        });
    }

    public function down(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            if (Schema::hasColumn('basic_settings', 'whatsapp_webhook_verify_token')) {
                $table->dropColumn('whatsapp_webhook_verify_token');
            }
        });
    }
};
