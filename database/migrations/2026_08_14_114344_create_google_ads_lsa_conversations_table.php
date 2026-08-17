<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'google_ads_lsa_conversations',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'google_ads_lsa_lead_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string(
                    'conversation_id',
                    100
                );

                $table->string(
                    'resource_name'
                );

                $table->string(
                    'channel',
                    50
                )->nullable();

                $table->string(
                    'participant_type',
                    50
                )->nullable();

                $table->unsignedBigInteger(
                    'call_duration_millis'
                )->nullable();

                $table->text(
                    'call_recording_url'
                )->nullable();

                $table->text(
                    'message_text'
                )->nullable();

                $table->text(
                    'attachment_urls'
                )->nullable();

                $table->timestamp(
                    'event_at'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'google_ads_lsa_lead_id',
                    'conversation_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'google_ads_lsa_conversations'
        );
    }
};