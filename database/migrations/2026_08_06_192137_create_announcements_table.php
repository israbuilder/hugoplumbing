<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('dashboard_id')
                ->nullable()
                ->constrained('dashboards')
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title', 180);
            $table->text('message');

            /*
             * info, success, warning, celebration.
             */
            $table->string('type', 40)
                ->default('info');

            $table->string('image_path')->nullable();
            $table->string('video_url')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('show_once')->default(false);

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            $table->unsignedInteger('duration_seconds')
                ->default(10);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'dashboard_id',
                'is_active',
                'starts_at',
                'ends_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};