<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'business_profile_locations',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'business_profile_account_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                /*
                 * Google resource:
                 * locations/123456789012345
                 */
                $table->string(
                    'location_name',
                    255
                );

                /*
                 * Numeric/unobfuscated listing ID.
                 */
                $table->string(
                    'location_id',
                    100
                );

                $table->string(
                    'title'
                )->nullable();

                $table->string(
                    'store_code'
                )->nullable();

                $table->string(
                    'phone'
                )->nullable();

                $table->text(
                    'website_uri'
                )->nullable();

                $table->string(
                    'primary_category'
                )->nullable();

                $table->string(
                    'address_line_1'
                )->nullable();

                $table->string(
                    'address_line_2'
                )->nullable();

                $table->string(
                    'city'
                )->nullable();

                $table->string(
                    'region'
                )->nullable();

                $table->string(
                    'postal_code'
                )->nullable();

                $table->string(
                    'country_code',
                    10
                )->nullable();

                $table->decimal(
                    'latitude',
                    10,
                    7
                )->nullable();

                $table->decimal(
                    'longitude',
                    10,
                    7
                )->nullable();

                $table->boolean(
                    'is_active'
                )->default(true);

                $table->boolean(
                    'is_primary'
                )->default(false);

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamp(
                    'last_synced_at'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'business_profile_account_id',
                    'location_id',
                ]);

                $table->index([
                    'business_profile_account_id',
                    'is_active',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'business_profile_locations'
        );
    }
};