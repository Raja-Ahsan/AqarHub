<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('properties')) {
            return;
        }
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'anomaly_checked_at')) {
                $table->timestamp('anomaly_checked_at')->nullable();
            }
            if (! Schema::hasColumn('properties', 'anomaly_review_suggested')) {
                $table->unsignedTinyInteger('anomaly_review_suggested')->default(0);
            }
            if (! Schema::hasColumn('properties', 'anomaly_flags')) {
                $table->json('anomaly_flags')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('properties')) {
            return;
        }
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'anomaly_flags')) {
                $table->dropColumn('anomaly_flags');
            }
            if (Schema::hasColumn('properties', 'anomaly_review_suggested')) {
                $table->dropColumn('anomaly_review_suggested');
            }
            if (Schema::hasColumn('properties', 'anomaly_checked_at')) {
                $table->dropColumn('anomaly_checked_at');
            }
        });
    }
};
