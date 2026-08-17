<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_tokens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('integration_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();

            $table->string('token_type', 50)->nullable();

            $table->json('scopes')->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('refreshed_at')->nullable();

            $table->timestamps();

            $table->unique('integration_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_tokens');
    }
};