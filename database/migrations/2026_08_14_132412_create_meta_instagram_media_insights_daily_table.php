<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'meta_instagram_media_insights_daily',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId('meta_instagram_media_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date('date');

                $table->unsignedBigInteger('reach')->default(0);

                $table->unsignedBigInteger('impressions')->default(0);

                $table->unsignedBigInteger('likes')->default(0);

                $table->unsignedBigInteger('comments')->default(0);

                $table->unsignedBigInteger('shares')->default(0);

                $table->unsignedBigInteger('saved')->default(0);

                $table->unsignedBigInteger('total_interactions')->default(0);

                $table->unsignedBigInteger('plays')->default(0);

                $table->json('metrics')->nullable();

                $table->json('raw')->nullable();

                $table->timestamps();

                $table->unique([
                    'meta_instagram_media_id',
                    'date'
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'meta_instagram_media_insights_daily'
        );
    }
};