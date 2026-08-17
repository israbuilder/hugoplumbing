<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_pages', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meta_connection_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('meta_page_id')->unique();

            $table->string('name');

            $table->string('category')->nullable();

            $table->string('username')->nullable();

            $table->text('page_access_token')->nullable();

            $table->string('instagram_business_account_id')
                ->nullable()
                ->index();

            $table->json('tasks')->nullable();

            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_pages');
    }
};