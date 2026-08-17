<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_console_sites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('integration_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('site_url', 2048);

            $table->string('property_type', 30)->nullable();

            $table->string('permission_level', 50)->nullable();

            $table->boolean('is_active')->default(true);

            $table->boolean('is_primary')->default(false);

            $table->timestamp('last_synced_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('integration_account_id');
            $table->index('is_active');

            $table->unique([
                'integration_account_id',
                'site_url',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_console_sites');
    }
};