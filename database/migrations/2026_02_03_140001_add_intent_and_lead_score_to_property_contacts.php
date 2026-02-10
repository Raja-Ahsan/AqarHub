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
            if (! Schema::hasColumn('property_contacts', 'intent')) {
                $table->string('intent', 32)->nullable()->after('message')->index();
            }
            if (! Schema::hasColumn('property_contacts', 'lead_score')) {
                $table->unsignedTinyInteger('lead_score')->nullable()->after('intent')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('property_contacts')) {
            return;
        }
        Schema::table('property_contacts', function (Blueprint $table) {
            if (Schema::hasColumn('property_contacts', 'lead_score')) {
                $table->dropColumn('lead_score');
            }
            if (Schema::hasColumn('property_contacts', 'intent')) {
                $table->dropColumn('intent');
            }
        });
    }
};
