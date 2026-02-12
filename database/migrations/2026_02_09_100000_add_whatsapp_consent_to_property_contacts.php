<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('property_contacts')) {
            return;
        }
        Schema::table('property_contacts', function (Blueprint $table) {
            if (! Schema::hasColumn('property_contacts', 'whatsapp_consent')) {
                $table->unsignedTinyInteger('whatsapp_consent')->default(0)->after('source')->comment('1 = opted in for WhatsApp updates');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('property_contacts')) {
            return;
        }
        Schema::table('property_contacts', function (Blueprint $table) {
            if (Schema::hasColumn('property_contacts', 'whatsapp_consent')) {
                $table->dropColumn('whatsapp_consent');
            }
        });
    }
};
