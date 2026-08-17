<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_connections', function (Blueprint $table) {

            $table->id();

            $table->foreignId('integration_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('meta_user_id')->nullable()->index();

            $table->string('name')->nullable();

            $table->text('access_token');

            $table->timestamp('token_expires_at')->nullable();

            $table->json('scopes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamp('last_synced_at')->nullable();

            $table->text('last_error')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_connections');
    }
};