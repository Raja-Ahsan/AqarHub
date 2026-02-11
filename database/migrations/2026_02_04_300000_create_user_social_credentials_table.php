<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_social_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('connectable_type');
            $table->unsignedBigInteger('connectable_id');
            $table->string('facebook_app_id', 500)->nullable();
            $table->string('facebook_app_secret', 500)->nullable();
            $table->string('linkedin_client_id', 500)->nullable();
            $table->string('linkedin_client_secret', 500)->nullable();
            $table->string('tiktok_client_key', 500)->nullable();
            $table->string('tiktok_client_secret', 500)->nullable();
            $table->string('twitter_client_id', 500)->nullable();
            $table->string('twitter_client_secret', 500)->nullable();
            $table->timestamps();

            $table->unique(['connectable_type', 'connectable_id'], 'user_social_credentials_connectable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_social_credentials');
    }
};
