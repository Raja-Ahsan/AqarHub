<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_social_credentials', function (Blueprint $table) {
            $table->string('whatsapp_phone_number_id', 100)->nullable()->after('whatsapp_channel_link');
            $table->string('whatsapp_business_account_id', 100)->nullable()->after('whatsapp_phone_number_id');
            $table->text('whatsapp_access_token')->nullable()->after('whatsapp_business_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_social_credentials', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_phone_number_id',
                'whatsapp_business_account_id',
                'whatsapp_access_token',
            ]);
        });
    }
};
