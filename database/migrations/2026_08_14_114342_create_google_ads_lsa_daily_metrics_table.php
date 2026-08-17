<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'google_ads_lsa_daily_metrics',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'google_ads_customer_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date('date');

                $table->decimal(
                    'average_weekly_budget',
                    14,
                    2
                )->nullable();

                $table->decimal(
                    'rating',
                    5,
                    2
                )->nullable();

                $table->unsignedInteger(
                    'review_count'
                )->nullable();

                $table->unsignedBigInteger(
                    'impressions_last_two_days'
                )->nullable();

                $table->decimal(
                    'phone_lead_responsiveness',
                    10,
                    6
                )->nullable();

                $table->unsignedInteger(
                    'charged_leads'
                )->default(0);

                $table->decimal(
                    'total_cost',
                    14,
                    2
                )->default(0);

                $table->string(
                    'currency_code',
                    10
                )->nullable();

                $table->unsignedInteger(
                    'phone_calls'
                )->default(0);

                $table->unsignedInteger(
                    'connected_phone_calls'
                )->default(0);

                $table->timestamp(
                    'synced_at'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'google_ads_customer_id',
                    'date',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'google_ads_lsa_daily_metrics'
        );
    }
};