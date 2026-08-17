<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_ads_campaigns', function (Blueprint $table) {

            $table->id();

            $table->foreignId('google_ads_customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('campaign_id', 50);

            $table->string('resource_name');

            $table->string('name')->nullable();

            $table->string('status', 50)->nullable();

            $table->string(
                'advertising_channel_type',
                100
            )->nullable();

            $table->string(
                'bidding_strategy_type',
                100
            )->nullable();

            $table->string(
                'budget_resource_name'
            )->nullable();

            $table->string(
                'budget_id',
                50
            )->nullable();

            $table->unsignedBigInteger(
                'budget_amount_micros'
            )->nullable();

            $table->string(
                'budget_period',
                50
            )->nullable();

            $table->boolean(
                'is_local_services'
            )->default(false);

            $table->boolean(
                'is_active'
            )->default(true);

            $table->json(
                'local_services_settings'
            )->nullable();

            $table->json(
                'metadata'
            )->nullable();

            $table->timestamp(
                'last_synced_at'
            )->nullable();

            $table->timestamps();

            $table->unique([
                'google_ads_customer_id',
                'campaign_id',
            ]);

            $table->index([
                'google_ads_customer_id',
                'is_local_services',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'google_ads_campaigns'
        );
    }
};