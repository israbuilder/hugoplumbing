<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_business_accounts', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meta_connection_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('meta_business_id')->unique();

            $table->string('name')->nullable();

            $table->string('verification_status')->nullable();

            $table->string('primary_page_id')->nullable();

            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_business_accounts');
    }
};