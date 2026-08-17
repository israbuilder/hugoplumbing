<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salespeople', function (Blueprint $table): void {
            $table->id();

            /*
             * Opcionalmente puede relacionarse con un usuario del sistema.
             */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('sales_team_id')
                ->nullable()
                ->constrained('sales_teams')
                ->nullOnDelete();

            $table->string('employee_number', 50)
                ->nullable()
                ->unique();

            $table->string('name', 150);
            $table->string('display_name', 100)->nullable();

            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();

            /*
             * Foto real y personaje usado en la carrera.
             */
            $table->string('photo_path')->nullable();
            $table->string('avatar_path')->nullable();

            /*
             * Configuración visual del avatar.
             */
            $table->string('avatar_color', 20)->nullable();
            $table->string('avatar_animation', 100)
                ->default('runner');

            $table->string('status', 30)
                ->default('active');

            /*
             * Permite ocultarlo del dashboard sin eliminarlo.
             */
            $table->boolean('show_on_dashboard')
                ->default(true);

            $table->unsignedInteger('sort_order')
                ->default(0);

            /*
             * Fecha en la que comenzó a participar.
             */
            $table->date('hire_date')->nullable();

            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'status',
                'show_on_dashboard',
                'sort_order',
            ]);

            $table->index([
                'sales_team_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salespeople');
    }
};