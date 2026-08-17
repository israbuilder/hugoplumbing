<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'google_ads_lsa_leads',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'google_ads_customer_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string(
                    'lead_id',
                    100
                );

                $table->string(
                    'resource_name'
                );

                $table->string(
                    'lead_type',
                    50
                )->nullable();

                $table->string(
                    'lead_status',
                    50
                )->nullable();

                $table->string(
                    'category_id'
                )->nullable();

                $table->string(
                    'service_id'
                )->nullable();

                $table->string(
                    'locale',
                    30
                )->nullable();

                /*
                 * Encrypted through model cast.
                 */
                $table->text(
                    'contact_phone'
                )->nullable();

                $table->text(
                    'consumer_name'
                )->nullable();

                $table->string(
                    'phone_extension',
                    20
                )->nullable();

                $table->boolean(
                    'lead_charged'
                )->default(false);

                $table->string(
                    'credit_state',
                    100
                )->nullable();

                $table->timestamp(
                    'credit_updated_at'
                )->nullable();

                $table->boolean(
                    'feedback_submitted'
                )->default(false);

                $table->text(
                    'note'
                )->nullable();

                $table->timestamp(
                    'lead_created_at'
                )->nullable();

                $table->timestamp(
                    'last_google_update_at'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'google_ads_customer_id',
                    'lead_id',
                ]);

                $table->index([
                    'google_ads_customer_id',
                    'lead_created_at',
                ]);

                $table->index([
                    'lead_type',
                    'lead_status',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'google_ads_lsa_leads'
        );
    }
};