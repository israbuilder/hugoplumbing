<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_rank_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('local_rank_scan_id')
                ->constrained('local_rank_scans')
                ->cascadeOnDelete();

            $table->foreignId('local_rank_grid_point_id')
                ->constrained('local_rank_grid_points')
                ->cascadeOnDelete();

            $table->boolean('found')->default(false);

            $table->unsignedSmallInteger('rank')->nullable();

            $table->string('business_name')->nullable();

            $table->string('place_id')->nullable()->index();

            $table->string('cid')->nullable()->index();

            $table->string('category')->nullable();

            $table->decimal('rating', 4, 2)->nullable();

            $table->unsignedInteger('reviews_count')->nullable();

            $table->string('address')->nullable();

            /*
             * Todos los resultados de Google Maps.
             * Aquí quedan los competidores.
             */
            $table->jsonb('items')->nullable();

            /*
             * Respuesta completa del provider.
             */
            $table->jsonb('raw_response')->nullable();

            $table->timestamps();

            $table->unique('local_rank_grid_point_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_rank_results');
    }
};