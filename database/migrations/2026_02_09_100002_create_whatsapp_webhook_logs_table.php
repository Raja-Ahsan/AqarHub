<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number_id', 100)->nullable();
            $table->string('from_wa_id', 50)->nullable();
            $table->string('message_type', 50)->nullable();
            $table->text('payload')->nullable();
            $table->unsignedTinyInteger('processed')->default(0)->comment('1=lead created');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_webhook_logs');
    }
};
