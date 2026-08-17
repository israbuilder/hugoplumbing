<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_teams', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 120);
            $table->string('slug', 140)->unique();

            $table->string('color', 20)->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('logo_path')->nullable();

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_teams');
    }
};