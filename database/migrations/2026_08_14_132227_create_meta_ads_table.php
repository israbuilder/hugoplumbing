<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ads', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meta_ad_set_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('meta_ad_id')->unique();

            $table->string('name');

            $table->string('status')->nullable();

            $table->string('effective_status')->nullable();

            $table->string('creative_id')->nullable()->index();

            $table->json('creative')->nullable();

            $table->json('tracking_specs')->nullable();

            $table->json('conversion_specs')->nullable();

            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ads');
    }
};