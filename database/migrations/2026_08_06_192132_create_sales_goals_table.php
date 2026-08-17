<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_goals', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('sales_team_id')
                ->nullable()
                ->constrained('sales_teams')
                ->nullOnDelete();

            $table->string('name', 150);
            $table->text('description')->nullable();

            /*
             * revenue, sales_count, calls, appointments,
             * contracts o points.
             */
            $table->string('goal_type', 40)
                ->default('revenue');

            /*
             * daily, weekly, monthly, quarterly, yearly o custom.
             */
            $table->string('period', 30)
                ->default('monthly');

            /*
             * Valor total de la meta.
             *
             * Para revenue puede ser 100000.00.
             * Para sales_count puede ser 100.
             */
            $table->decimal('target_value', 15, 2);

            $table->string('currency', 3)
                ->default('USD');

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            /*
             * Meta activa visible en el dashboard.
             */
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_dashboard')->default(true);

            /*
             * Una meta marcada como principal puede aparecer como
             * la línea de llegada de la carrera.
             */
            $table->boolean('is_primary')->default(false);

            /*
             * Configuraciones como premios, colores o niveles.
             */
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'is_active',
                'show_on_dashboard',
                'starts_at',
                'ends_at',
            ]);

            $table->index([
                'goal_type',
                'period',
            ]);

            $table->index([
                'sales_team_id',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_goals');
    }
};