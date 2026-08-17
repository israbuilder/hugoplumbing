<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'business_profile_accounts',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'integration_account_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                /*
                 * Example:
                 * accounts/123456789
                 */
                $table->string(
                    'account_name',
                    255
                );

                $table->string(
                    'account_id',
                    100
                );

                $table->string(
                    'display_name'
                )->nullable();

                $table->string(
                    'account_type',
                    100
                )->nullable();

                $table->string(
                    'role',
                    100
                )->nullable();

                $table->string(
                    'verification_state',
                    100
                )->nullable();

                $table->boolean(
                    'is_active'
                )->default(true);

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamp(
                    'last_synced_at'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'integration_account_id',
                    'account_id',
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
            'business_profile_accounts'
        );
    }
};