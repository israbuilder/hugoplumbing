<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('salesperson_id')
                ->constrained('salespeople')
                ->cascadeOnDelete();

            $table->foreignId('sales_goal_id')
                ->nullable()
                ->constrained('sales_goals')
                ->nullOnDelete();

            $table->foreignId('sale_id')
                ->nullable()
                ->constrained('sales')
                ->nullOnDelete();

            $table->string('type', 60);
            $table->string('title', 180);
            $table->text('description')->nullable();

            $table->decimal('value', 15, 2)->nullable();
            $table->string('icon', 100)->nullable();

            $table->dateTime('achieved_at');

            /*
             * Evita otorgar dos veces un logro generado
             * automáticamente.
             */
            $table->string('deduplication_key', 190)
                ->nullable()
                ->unique();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index([
                'salesperson_id',
                'type',
                'achieved_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};