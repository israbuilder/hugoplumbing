<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'google_ads_campaign_daily_metrics',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'google_ads_campaign_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date('date');

                $table->unsignedBigInteger(
                    'impressions'
                )->default(0);

                $table->unsignedBigInteger(
                    'clicks'
                )->default(0);

                $table->unsignedBigInteger(
                    'cost_micros'
                )->default(0);

                $table->decimal(
                    'conversions',
                    16,
                    4
                )->default(0);

                $table->decimal(
                    'all_conversions',
                    16,
                    4
                )->default(0);

                $table->decimal(
                    'conversion_value',
                    16,
                    2
                )->default(0);

                $table->timestamp(
                    'synced_at'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'google_ads_campaign_id',
                    'date',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'google_ads_campaign_daily_metrics'
        );
    }
};