<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('ai_assistant_status')->default(1)->after('theme_version')->comment('1=enabled, 0=disabled');
        });
    }

    public function down(): void
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn('ai_assistant_status');
        });
    }
};
