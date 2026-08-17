<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ad_insights_daily', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meta_ad_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('level', 30);

            $table->string('meta_campaign_id')->nullable()->index();

            $table->string('meta_ad_set_id')->nullable()->index();

            $table->string('meta_ad_id')->nullable()->index();

            $table->date('date');

            $table->unsignedBigInteger('impressions')->default(0);

            $table->unsignedBigInteger('reach')->default(0);

            $table->unsignedBigInteger('clicks')->default(0);

            $table->unsignedBigInteger('unique_clicks')->default(0);

            $table->unsignedBigInteger('inline_link_clicks')->default(0);

            $table->decimal('spend', 14, 4)->default(0);

            $table->decimal('cpc', 14, 6)->nullable();

            $table->decimal('cpm', 14, 6)->nullable();

            $table->decimal('ctr', 14, 6)->nullable();

            $table->decimal('frequency', 14, 6)->nullable();

            $table->json('actions')->nullable();

            $table->json('action_values')->nullable();

            $table->json('cost_per_action_type')->nullable();

            $table->json('outbound_clicks')->nullable();

            $table->json('outbound_clicks_ctr')->nullable();

            $table->string('quality_ranking')->nullable();

            $table->string('engagement_rate_ranking')->nullable();

            $table->string('conversion_rate_ranking')->nullable();

            $table->json('raw')->nullable();

            $table->timestamps();

            $table->unique([
                'meta_ad_account_id',
                'level',
                'meta_campaign_id',
                'meta_ad_set_id',
                'meta_ad_id',
                'date'
            ], 'meta_insights_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ad_insights_daily');
    }
};