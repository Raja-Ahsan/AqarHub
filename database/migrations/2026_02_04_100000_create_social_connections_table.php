<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('social_connections')) {
            Schema::create('social_connections', function (Blueprint $table) {
                $table->id();
                $table->string('connectable_type');
                $table->unsignedBigInteger('connectable_id');
                $table->string('platform', 50);
                $table->text('access_token')->nullable();
                $table->text('refresh_token')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->string('platform_user_id', 100)->nullable();
                $table->string('platform_username', 255)->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['connectable_type', 'connectable_id']);
                $table->unique(['connectable_type', 'connectable_id', 'platform'], 'social_conn_user_platform_unique');
            });
        } else {
            Schema::table('social_connections', function (Blueprint $table) {
                $table->unique(['connectable_type', 'connectable_id', 'platform'], 'social_conn_user_platform_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_connections');
    }
};
