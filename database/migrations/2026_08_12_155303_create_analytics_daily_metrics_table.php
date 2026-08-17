<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'analytics_daily_metrics',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'analytics_property_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date('date');

                $table->unsignedBigInteger(
                    'active_users'
                )->default(0);

                $table->unsignedBigInteger(
                    'total_users'
                )->default(0);

                $table->unsignedBigInteger(
                    'new_users'
                )->default(0);

                $table->unsignedBigInteger(
                    'sessions'
                )->default(0);

                $table->unsignedBigInteger(
                    'engaged_sessions'
                )->default(0);

                $table->decimal(
                    'engagement_rate',
                    12,
                    8
                )->default(0);

                $table->decimal(
                    'average_session_duration',
                    16,
                    4
                )->default(0);

                $table->unsignedBigInteger(
                    'screen_page_views'
                )->default(0);

                $table->unsignedBigInteger(
                    'event_count'
                )->default(0);

                /*
                 * GA4 now calls conversions
                 * Key Events.
                 */
                $table->decimal(
                    'key_events',
                    16,
                    4
                )->default(0);

                $table->decimal(
                    'total_revenue',
                    16,
                    2
                )->default(0);

                $table->timestamp(
                    'synced_at'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'analytics_property_id',
                    'date',
                ]);

                $table->index([
                    'analytics_property_id',
                    'date',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'analytics_daily_metrics'
        );
    }
};