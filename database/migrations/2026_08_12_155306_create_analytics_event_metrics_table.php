<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'analytics_event_metrics',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'analytics_property_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date('date');

                $table->string(
                    'event_name',
                    255
                );

                $table->unsignedBigInteger(
                    'event_count'
                )->default(0);

                $table->unsignedBigInteger(
                    'total_users'
                )->default(0);

                $table->decimal(
                    'key_events',
                    16,
                    4
                )->default(0);

                $table->decimal(
                    'event_value',
                    16,
                    4
                )->default(0);

                $table->timestamp(
                    'synced_at'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'analytics_property_id',
                    'date',
                    'event_name',
                ]);

                $table->index([
                    'analytics_property_id',
                    'date',
                ]);

                $table->index(
                    'event_name'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'analytics_event_metrics'
        );
    }
};