<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_instagram_accounts', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meta_page_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('meta_instagram_id')->unique();

            $table->string('username')->nullable();

            $table->string('name')->nullable();

            $table->string('profile_picture_url', 2048)->nullable();

            $table->unsignedBigInteger('followers_count')->default(0);

            $table->unsignedBigInteger('follows_count')->default(0);

            $table->unsignedBigInteger('media_count')->default(0);

            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_instagram_accounts');
    }
};