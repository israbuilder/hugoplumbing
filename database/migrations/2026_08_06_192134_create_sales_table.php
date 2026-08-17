<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('salesperson_id')
                ->constrained('salespeople')
                ->restrictOnDelete();

            /*
             * Meta relacionada. Es opcional porque una venta puede
             * contarse automáticamente por rango de fechas.
             */
            $table->foreignId('sales_goal_id')
                ->nullable()
                ->constrained('sales_goals')
                ->nullOnDelete();

            /*
             * Usuario administrativo que registró la venta.
             */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Referencia interna, factura, orden o contrato.
             */
            $table->string('reference_number', 100)->nullable();

            /*
             * Sistema de origen:
             * manual, crm, hubspot, salesforce, webhook, csv, etc.
             */
            $table->string('source', 60)->default('manual');
            $table->string('external_id', 150)->nullable();

            $table->string('customer_name', 150)->nullable();
            $table->string('description')->nullable();

            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');

            /*
             * Puntos adicionales para gamificación.
             */
            $table->decimal('points', 12, 2)->default(0);

            /*
             * Cantidades adicionales para dashboards de actividad.
             */
            $table->unsignedInteger('calls_count')->default(0);
            $table->unsignedInteger('appointments_count')->default(0);
            $table->unsignedInteger('contracts_count')->default(0);

            $table->string('status', 30)
                ->default('approved');

            /*
             * Momento real de la venta.
             */
            $table->dateTime('sold_at');

            /*
             * Momento de aprobación, cancelación o reembolso.
             */
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('refunded_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            /*
             * Evita recibir dos veces la misma venta desde un CRM.
             */
            $table->unique([
                'source',
                'external_id',
            ]);

            $table->index([
                'salesperson_id',
                'status',
                'sold_at',
            ]);

            $table->index([
                'sales_goal_id',
                'status',
            ]);

            $table->index([
                'status',
                'sold_at',
            ]);

            $table->index('reference_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};