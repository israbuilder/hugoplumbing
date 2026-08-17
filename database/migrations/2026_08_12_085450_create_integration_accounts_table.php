<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('integration_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('external_account_id')->nullable();

            $table->string('name')->nullable();
            $table->string('email')->nullable();

            $table->string('status', 30)->default('connected');

            $table->json('metadata')->nullable();

            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            $table->index([
                'integration_id',
                'status',
            ]);

            $table->unique([
                'integration_id',
                'external_account_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_accounts');
    }
};