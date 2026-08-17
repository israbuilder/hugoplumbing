<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_page_posts', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meta_page_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('meta_post_id')->unique();

            $table->text('message')->nullable();

            $table->text('permalink_url')->nullable();

            $table->text('link')->nullable();

            $table->string('post_type')->nullable();

            $table->unsignedBigInteger('shares')->default(0);

            $table->timestamp('published_at')->nullable();

            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_page_posts');
    }
};