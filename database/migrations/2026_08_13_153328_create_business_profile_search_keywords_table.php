<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'business_profile_search_keywords',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'business_profile_location_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                /*
                 * We store the first day of the month.
                 */
                $table->date('month');

                $table->text(
                    'keyword'
                );

                /*
                 * Google can return either
                 * a direct value or threshold.
                 */
                $table->unsignedBigInteger(
                    'impressions'
                )->nullable();

                $table->unsignedBigInteger(
                    'threshold'
                )->nullable();

                $table->char(
                    'keyword_hash',
                    64
                );

                $table->timestamp(
                    'synced_at'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'business_profile_location_id',
                    'month',
                    'keyword_hash',
                ], 'gbp_keyword_unique');

                $table->index([
                    'business_profile_location_id',
                    'month',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'business_profile_search_keywords'
        );
    }
};