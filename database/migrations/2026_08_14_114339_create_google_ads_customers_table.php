<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_ads_customers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('integration_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('customer_id', 30);

            $table->string('descriptive_name')->nullable();

            $table->string('currency_code', 10)->nullable();

            $table->string('time_zone')->nullable();

            $table->boolean('is_manager')->default(false);

            $table->boolean('is_test_account')->default(false);

            $table->boolean('is_active')->default(true);

            $table->boolean('is_primary')->default(false);

            $table->json('metadata')->nullable();

            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            $table->unique([
                'integration_account_id',
                'customer_id',
            ]);

            $table->index([
                'integration_account_id',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_ads_customers');
    }
};