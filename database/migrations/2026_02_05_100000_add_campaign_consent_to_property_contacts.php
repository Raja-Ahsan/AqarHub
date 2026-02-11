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
            if (! Schema::hasColumn('property_contacts', 'unsubscribed_at')) {
                $table->timestamp('unsubscribed_at')->nullable()->after('reply_sent_at');
            }
            if (! Schema::hasColumn('property_contacts', 'unsubscribe_token')) {
                $table->string('unsubscribe_token', 64)->nullable()->unique()->after('unsubscribed_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('property_contacts')) {
            return;
        }
        Schema::table('property_contacts', function (Blueprint $table) {
            if (Schema::hasColumn('property_contacts', 'unsubscribe_token')) {
                $table->dropColumn('unsubscribe_token');
            }
            if (Schema::hasColumn('property_contacts', 'unsubscribed_at')) {
                $table->dropColumn('unsubscribed_at');
            }
        });
    }
};
