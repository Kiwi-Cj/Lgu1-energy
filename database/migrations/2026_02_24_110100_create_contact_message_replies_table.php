<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_message_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_message_id')->constrained('contact_messages')->cascadeOnDelete();
            $table->unsignedBigInteger('sent_by_user_id')->nullable();
            $table->string('recipient_email');
            $table->string('subject');
            $table->longText('message');
            $table->json('attachments')->nullable();
            $table->string('send_status', 20)->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['contact_message_id', 'created_at']);
            $table->index('sent_by_user_id');
            $table->index('send_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_message_replies');
    }
};
