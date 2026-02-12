<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_social_credentials', function (Blueprint $table) {
            $table->string('whatsapp_phone_number', 50)->nullable()->after('twitter_client_secret');
            $table->string('whatsapp_channel_link', 500)->nullable()->after('whatsapp_phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('user_social_credentials', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_phone_number', 'whatsapp_channel_link']);
        });
    }
};
