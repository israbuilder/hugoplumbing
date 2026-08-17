<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ad_sets', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meta_campaign_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('meta_ad_set_id')->unique();

            $table->string('name');

            $table->string('status')->nullable();

            $table->string('effective_status')->nullable();

            $table->string('optimization_goal')->nullable();

            $table->string('billing_event')->nullable();

            $table->decimal('bid_amount', 16, 2)->nullable();

            $table->decimal('daily_budget', 16, 2)->nullable();

            $table->decimal('lifetime_budget', 16, 2)->nullable();

            $table->timestamp('start_time')->nullable();

            $table->timestamp('end_time')->nullable();

            $table->json('targeting')->nullable();

            $table->json('promoted_object')->nullable();

            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ad_sets');
    }
};