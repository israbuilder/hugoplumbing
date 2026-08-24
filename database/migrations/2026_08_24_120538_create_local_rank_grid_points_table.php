<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_rank_grid_points', function (Blueprint $table) {
            $table->id();

            $table->foreignId('local_rank_scan_id')
                ->constrained('local_rank_scans')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('row');
            $table->unsignedSmallInteger('column');

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->decimal('distance_miles', 10, 4)
                ->default(0);

            $table->boolean('is_center')->default(false);

            $table->string('status')->default('pending')->index();

            $table->string('provider_task_id')
                ->nullable()
                ->index();

            $table->unsignedSmallInteger('attempts')
                ->default(0);

            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->unique(
                ['local_rank_scan_id', 'row', 'column'],
                'local_rank_point_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_rank_grid_points');
    }
};