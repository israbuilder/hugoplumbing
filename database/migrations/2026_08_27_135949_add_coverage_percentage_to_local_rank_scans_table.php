<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'local_rank_scans',
            function (Blueprint $table) {
                $table->decimal(
                    'coverage_percentage',
                    8,
                    2
                )
                    ->nullable()
                    ->after('average_rank');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'local_rank_scans',
            function (Blueprint $table) {
                $table->dropColumn(
                    'coverage_percentage'
                );
            }
        );
    }
};