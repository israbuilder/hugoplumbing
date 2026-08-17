<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'search_console_backfills',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'search_console_site_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date('date_from');
                $table->date('date_to');

                $table->string(
                    'status',
                    30
                )->default('pending');

                $table->unsignedInteger(
                    'total_chunks'
                )->default(0);

                $table->unsignedInteger(
                    'completed_chunks'
                )->default(0);

                $table->unsignedInteger(
                    'failed_chunks'
                )->default(0);

                $table->unsignedBigInteger(
                    'rows_processed'
                )->default(0);

                $table->text(
                    'error_message'
                )->nullable();

                $table->timestamp(
                    'started_at'
                )->nullable();

                $table->timestamp(
                    'finished_at'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'search_console_site_id',
                    'status',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'search_console_backfills'
        );
    }
};