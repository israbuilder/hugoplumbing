<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_slides', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('dashboard_id')
                ->constrained('dashboards')
                ->cascadeOnDelete();

            /*
             * Meta que utiliza este slide.
             */
            $table->foreignId('sales_goal_id')
                ->nullable()
                ->constrained('sales_goals')
                ->nullOnDelete();

            /*
             * Equipo que se mostrará, si el slide se limita
             * a un equipo particular.
             */
            $table->foreignId('sales_team_id')
                ->nullable()
                ->constrained('sales_teams')
                ->nullOnDelete();

            $table->string('name', 150);

            /*
             * race, leaderboard, daily_sales, goal_progress,
             * top_performer, team_comparison, announcement o custom.
             */
            $table->string('type', 50);

            $table->string('title', 180)->nullable();
            $table->string('subtitle', 255)->nullable();

            /*
             * Segundos que permanecerá visible.
             */
            $table->unsignedInteger('duration_seconds')
                ->default(15);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            /*
             * Configuración por slide:
             * cantidad de vendedores, animación, fondo,
             * mostrar dinero, mostrar porcentaje, etc.
             */
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index([
                'dashboard_id',
                'is_active',
                'sort_order',
            ]);

            $table->index([
                'type',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_slides');
    }
};