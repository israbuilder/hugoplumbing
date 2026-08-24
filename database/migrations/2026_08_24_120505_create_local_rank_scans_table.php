<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_rank_scans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('local_rank_location_id')
                ->constrained('local_rank_locations')
                ->cascadeOnDelete();

            $table->foreignId('local_rank_keyword_id')
                ->constrained('local_rank_keywords')
                ->cascadeOnDelete();

            $table->string('status')->default('pending')->index();

            $table->unsignedSmallInteger('grid_size');

            $table->decimal('radius_miles', 8, 2);

            $table->unsignedSmallInteger('zoom')->default(15);

            $table->decimal('center_latitude', 10, 7);
            $table->decimal('center_longitude', 10, 7);

            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('completed_points')->default(0);
            $table->unsignedInteger('failed_points')->default(0);

            $table->decimal('average_rank', 8, 2)->nullable();

            $table->decimal('top_3_percentage', 8, 2)->nullable();
            $table->decimal('top_10_percentage', 8, 2)->nullable();
            $table->decimal('visibility_score', 8, 2)->nullable();

            $table->decimal('provider_cost', 12, 6)->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->jsonb('meta')->nullable();

            $table->timestamps();

            $table->index([
                'local_rank_location_id',
                'local_rank_keyword_id',
                'created_at',
            ], 'local_rank_scan_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_rank_scans');
    }
};