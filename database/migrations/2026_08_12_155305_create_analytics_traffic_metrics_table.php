<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'analytics_traffic_metrics',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'analytics_property_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date('date');

                $table->string(
                    'source',
                    255
                )->nullable();

                $table->string(
                    'medium',
                    255
                )->nullable();

                $table->string(
                    'campaign',
                    500
                )->nullable();

                $table->string(
                    'channel_group',
                    255
                )->nullable();

                $table->text(
                    'landing_page'
                )->nullable();

                $table->unsignedBigInteger(
                    'active_users'
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
                    'key_events',
                    16,
                    4
                )->default(0);

                $table->decimal(
                    'total_revenue',
                    16,
                    2
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
                    'dimension_hash',
                ], 'analytics_traffic_metrics_unique');

                $table->index([
                    'analytics_property_id',
                    'date',
                ]);

                $table->index([
                    'source',
                    'medium',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'analytics_traffic_metrics'
        );
    }
};