<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_console_metrics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('search_console_site_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('date');

            /*
             * Nivel de agregación.
             *
             * site
             * query
             * page
             * query_page
             * country
             * device
             * query_country_device
             *
             * etc.
             */
            $table->string('grain', 50);

            /*
             * web
             * image
             * video
             * news
             * discover
             * etc.
             */
            $table->string('search_type', 30)
                ->default('web');

            /*
             * final
             * all
             * etc.
             */
            $table->string('data_state', 30)
                ->nullable();

            $table->text('query')->nullable();

            $table->text('page')->nullable();

            $table->string('country', 10)->nullable();

            $table->string('device', 30)->nullable();

            $table->string('search_appearance', 100)->nullable();

            /*
             * Métricas Search Console
             */
            $table->unsignedBigInteger('clicks')
                ->default(0);

            $table->unsignedBigInteger('impressions')
                ->default(0);

            $table->decimal('ctr', 12, 8)
                ->default(0);

            $table->decimal('position', 12, 4)
                ->default(0);

            /*
             * SHA-256 de las dimensiones.
             *
             * Nos permite hacer upsert sin crear
             * un índice gigantesco sobre query + page.
             */
            $table->char('dimension_hash', 64);

            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            $table->unique([
                'search_console_site_id',
                'date',
                'grain',
                'search_type',
                'dimension_hash',
            ], 'sc_metrics_unique');

            $table->index([
                'search_console_site_id',
                'date',
            ]);

            $table->index([
                'search_console_site_id',
                'grain',
                'date',
            ]);

            $table->index('dimension_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_console_metrics');
    }
};