<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'sales_goal_participants',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('sales_goal_id')
                    ->constrained('sales_goals')
                    ->cascadeOnDelete();

                $table->foreignId('salesperson_id')
                    ->constrained('salespeople')
                    ->cascadeOnDelete();

                /*
                 * Si es null, se utiliza target_value de sales_goals.
                 */
                $table->decimal('target_value', 15, 2)
                    ->nullable();

                /*
                 * Valor inicial por si una campaña comienza con
                 * ventas ya acumuladas.
                 */
                $table->decimal('starting_value', 15, 2)
                    ->default(0);

                $table->boolean('is_active')->default(true);

                $table->json('settings')->nullable();

                $table->timestamps();

                $table->unique([
                    'sales_goal_id',
                    'salesperson_id',
                ]);

                $table->index([
                    'salesperson_id',
                    'is_active',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_goal_participants');
    }
};