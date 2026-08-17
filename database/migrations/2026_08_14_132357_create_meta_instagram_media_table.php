<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_instagram_media', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meta_instagram_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('meta_media_id')->unique();

            $table->string('media_type')->nullable();

            $table->string('media_product_type')->nullable();

            $table->text('caption')->nullable();

            $table->text('permalink')->nullable();

            $table->text('media_url')->nullable();

            $table->text('thumbnail_url')->nullable();

            $table->timestamp('published_at')->nullable();

            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_instagram_media');
    }
};