<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (! Schema::hasColumn('vendors', 'auto_reply_enabled')) {
                    $table->unsignedTinyInteger('auto_reply_enabled')->default(0);
                }
                if (! Schema::hasColumn('vendors', 'auto_reply_after_hours')) {
                    $table->unsignedSmallInteger('auto_reply_after_hours')->nullable();
                }
                if (! Schema::hasColumn('vendors', 'auto_reply_message')) {
                    $table->text('auto_reply_message')->nullable();
                }
            });
        }

        if (Schema::hasTable('agents')) {
            Schema::table('agents', function (Blueprint $table) {
                if (! Schema::hasColumn('agents', 'auto_reply_enabled')) {
                    $table->unsignedTinyInteger('auto_reply_enabled')->default(0);
                }
                if (! Schema::hasColumn('agents', 'auto_reply_after_hours')) {
                    $table->unsignedSmallInteger('auto_reply_after_hours')->nullable();
                }
                if (! Schema::hasColumn('agents', 'auto_reply_message')) {
                    $table->text('auto_reply_message')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (Schema::hasColumn('vendors', 'auto_reply_message')) {
                    $table->dropColumn('auto_reply_message');
                }
                if (Schema::hasColumn('vendors', 'auto_reply_after_hours')) {
                    $table->dropColumn('auto_reply_after_hours');
                }
                if (Schema::hasColumn('vendors', 'auto_reply_enabled')) {
                    $table->dropColumn('auto_reply_enabled');
                }
            });
        }

        if (Schema::hasTable('agents')) {
            Schema::table('agents', function (Blueprint $table) {
                if (Schema::hasColumn('agents', 'auto_reply_message')) {
                    $table->dropColumn('auto_reply_message');
                }
                if (Schema::hasColumn('agents', 'auto_reply_after_hours')) {
                    $table->dropColumn('auto_reply_after_hours');
                }
                if (Schema::hasColumn('agents', 'auto_reply_enabled')) {
                    $table->dropColumn('auto_reply_enabled');
                }
            });
        }
    }
};
