<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'analytics_page_metrics',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'analytics_property_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date('date');

                /*
                 * page
                 * landing_page
                 */
                $table->string(
                    'grain',
                    30
                );

                $table->text(
                    'page_path'
                )->nullable();

                $table->text(
                    'page_title'
                )->nullable();

                $table->text(
                    'landing_page'
                )->nullable();

                $table->unsignedBigInteger(
                    'active_users'
                )->default(0);

                $table->unsignedBigInteger(
                    'sessions'
                )->default(0);

                $table->unsignedBigInteger(
                    'engaged_sessions'
                )->default(0);

                $table->unsignedBigInteger(
                    'screen_page_views'
                )->default(0);

                $table->unsignedBigInteger(
                    'event_count'
                )->default(0);

                $table->decimal(
                    'key_events',
                    16,
                    4
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

                $table->char(
                    'dimension_hash',
                    64
                );

                $table->timestamp(
                    'synced_at'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'analytics_property_id',
                    'date',
                    'grain',
                    'dimension_hash',
                ], 'analytics_page_metrics_unique');

                $table->index([
                    'analytics_property_id',
                    'date',
                    'grain',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'analytics_page_metrics'
        );
    }
};