<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_social_credentials', function (Blueprint $table) {
            $table->string('whatsapp_alert_wa_id', 50)->nullable()->after('whatsapp_access_token');
        });
    }

    public function down(): void
    {
        Schema::table('user_social_credentials', function (Blueprint $table) {
            $table->dropColumn('whatsapp_alert_wa_id');
        });
    }
};
