<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'meta_page_post_insights_daily',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId('meta_page_post_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date('date');

                $table->unsignedBigInteger('impressions')->default(0);

                $table->unsignedBigInteger('reach')->default(0);

                $table->unsignedBigInteger('engaged_users')->default(0);

                $table->unsignedBigInteger('clicks')->default(0);

                $table->unsignedBigInteger('reactions')->default(0);

                $table->unsignedBigInteger('comments')->default(0);

                $table->unsignedBigInteger('shares')->default(0);

                $table->json('metrics')->nullable();

                $table->json('raw')->nullable();

                $table->timestamps();

                $table->unique([
                    'meta_page_post_id',
                    'date'
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'meta_page_post_insights_daily'
        );
    }
};