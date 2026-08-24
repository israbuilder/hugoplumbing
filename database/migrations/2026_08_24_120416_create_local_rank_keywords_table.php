<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_rank_keywords', function (Blueprint $table) {
            $table->id();

            $table->foreignId('local_rank_location_id')
                ->constrained('local_rank_locations')
                ->cascadeOnDelete();

            $table->string('keyword');

            $table->string('service')->nullable();

            $table->boolean('is_active')->default(true);

            $table->unsignedSmallInteger('default_grid_size')
                ->default(5);

            $table->decimal('default_radius_miles', 8, 2)
                ->default(5);

            $table->unsignedSmallInteger('zoom')
                ->default(15);

            $table->timestamps();

            $table->unique(
                ['local_rank_location_id', 'keyword'],
                'local_rank_keyword_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_rank_keywords');
    }
};