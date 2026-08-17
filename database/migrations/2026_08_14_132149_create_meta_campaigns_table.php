<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_campaigns', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meta_ad_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('meta_campaign_id')->unique();

            $table->string('name');

            $table->string('objective')->nullable();

            $table->string('status')->nullable();

            $table->string('effective_status')->nullable();

            $table->string('buying_type')->nullable();

            $table->string('special_ad_category')->nullable();

            $table->decimal('daily_budget', 16, 2)->nullable();

            $table->decimal('lifetime_budget', 16, 2)->nullable();

            $table->timestamp('start_time')->nullable();

            $table->timestamp('stop_time')->nullable();

            $table->timestamp('meta_created_time')->nullable();

            $table->timestamp('meta_updated_time')->nullable();

            $table->json('raw')->nullable();

            $table->timestamps();

            $table->index([
                'meta_ad_account_id',
                'effective_status'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_campaigns');
    }
};