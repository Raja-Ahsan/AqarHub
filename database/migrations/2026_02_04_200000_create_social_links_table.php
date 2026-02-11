<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('connectable_type');
            $table->unsignedBigInteger('connectable_id');
            $table->string('facebook_url', 500)->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->string('instagram_url', 500)->nullable();
            $table->string('tiktok_url', 500)->nullable();
            $table->string('twitter_url', 500)->nullable();
            $table->timestamps();

            $table->unique(['connectable_type', 'connectable_id'], 'social_links_connectable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
