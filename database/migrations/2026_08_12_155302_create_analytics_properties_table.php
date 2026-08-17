<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'analytics_properties',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'integration_account_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                /*
                 * Numeric GA4 property ID.
                 *
                 * Example:
                 * 123456789
                 */
                $table->string(
                    'property_id',
                    50
                );

                /*
                 * Google resource name.
                 *
                 * properties/123456789
                 */
                $table->string(
                    'property_name'
                );

                $table->string(
                    'display_name'
                )->nullable();

                /*
                 * Parent Analytics account.
                 */
                $table->string(
                    'account_id',
                    50
                )->nullable();

                $table->string(
                    'account_name'
                )->nullable();

                $table->string(
                    'time_zone'
                )->nullable();

                $table->string(
                    'currency_code',
                    10
                )->nullable();

                $table->string(
                    'property_type',
                    50
                )->nullable();

                $table->boolean(
                    'is_active'
                )->default(true);

                $table->boolean(
                    'is_primary'
                )->default(false);

                $table->timestamp(
                    'last_synced_at'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'integration_account_id',
                    'property_id',
                ]);

                $table->index([
                    'integration_account_id',
                    'is_active',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'analytics_properties'
        );
    }
};