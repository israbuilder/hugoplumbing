<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboards', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 150);
            $table->string('slug', 160)->unique();

            /*
             * Token para abrir la pantalla sin iniciar sesión.
             */
            $table->string('access_token', 80)
                ->unique();

            $table->string('timezone', 80)
                ->default('America/Chicago');

            $table->string('currency', 3)
                ->default('USD');

            /*
             * light, dark o company.
             */
            $table->string('theme', 40)
                ->default('dark');

            $table->boolean('is_active')->default(true);

            /*
             * Tiempo predeterminado entre slides.
             */
            $table->unsignedInteger('default_slide_duration')
                ->default(15);

            /*
             * Segundos entre actualizaciones de datos.
             */
            $table->unsignedInteger('refresh_interval')
                ->default(10);

            /*
             * Configuración visual:
             * logo, colores, sonidos, confeti, etc.
             */
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboards');
    }
};