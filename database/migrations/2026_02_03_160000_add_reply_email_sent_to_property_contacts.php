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
            if (! Schema::hasColumn('property_contacts', 'reply_email_sent')) {
                $table->unsignedTinyInteger('reply_email_sent')->default(0)->index();
            }
            if (! Schema::hasColumn('property_contacts', 'reply_sent_at')) {
                $table->timestamp('reply_sent_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('property_contacts')) {
            return;
        }
        Schema::table('property_contacts', function (Blueprint $table) {
            if (Schema::hasColumn('property_contacts', 'reply_sent_at')) {
                $table->dropColumn('reply_sent_at');
            }
            if (Schema::hasColumn('property_contacts', 'reply_email_sent')) {
                $table->dropColumn('reply_email_sent');
            }
        });
    }
};
