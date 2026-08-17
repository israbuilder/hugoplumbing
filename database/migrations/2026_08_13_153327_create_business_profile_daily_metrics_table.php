<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'business_profile_daily_metrics',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'business_profile_location_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date('date');

                $table->string(
                    'metric',
                    150
                );

                $table->unsignedBigInteger(
                    'value'
                )->default(0);

                $table->json(
                    'sub_entity'
                )->nullable();

                $table->char(
                    'dimension_hash',
                    64
                );

                $table->timestamp(
                    'synced_at'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'business_profile_location_id',
                    'date',
                    'metric',
                    'dimension_hash',
                ], 'gbp_daily_metrics_unique');

                $table->index([
                    'business_profile_location_id',
                    'date',
                ]);

                $table->index([
                    'metric',
                    'date',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'business_profile_daily_metrics'
        );
    }
};