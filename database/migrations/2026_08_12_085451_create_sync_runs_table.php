<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('integration_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type', 100);

            $table->string('status', 30)
                ->default('pending');

            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();

            $table->unsignedBigInteger('rows_processed')
                ->default(0);

            $table->unsignedBigInteger('rows_created')
                ->default(0);

            $table->unsignedBigInteger('rows_updated')
                ->default(0);

            $table->text('error_message')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->index([
                'integration_account_id',
                'type',
                'status',
            ]);

            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
    }
};