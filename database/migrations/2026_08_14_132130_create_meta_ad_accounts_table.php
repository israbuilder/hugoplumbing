<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ad_accounts', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meta_connection_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('meta_business_account_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('meta_ad_account_id')->unique();

            $table->string('account_id')->nullable()->index();

            $table->string('name')->nullable();

            $table->string('currency', 10)->nullable();

            $table->string('timezone_name')->nullable();

            $table->integer('timezone_offset_hours_utc')->nullable();

            $table->integer('account_status')->nullable();

            $table->integer('disable_reason')->nullable();

            $table->decimal('balance', 16, 2)->nullable();

            $table->decimal('amount_spent', 16, 2)->nullable();

            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ad_accounts');
    }
};