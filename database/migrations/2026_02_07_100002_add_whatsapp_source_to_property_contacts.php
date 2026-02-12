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
            if (! Schema::hasColumn('property_contacts', 'source')) {
                $table->string('source', 50)->default('website')->after('message');
            }
            if (! Schema::hasColumn('property_contacts', 'whatsapp_wa_id')) {
                $table->string('whatsapp_wa_id', 50)->nullable()->after('source');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('property_contacts')) {
            return;
        }
        Schema::table('property_contacts', function (Blueprint $table) {
            if (Schema::hasColumn('property_contacts', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('property_contacts', 'whatsapp_wa_id')) {
                $table->dropColumn('whatsapp_wa_id');
            }
        });
    }
};
