<?php

namespace Database\Seeders;

use App\Models\Integration;
use Illuminate\Database\Seeder;

class MetaIntegrationSeeder extends Seeder
{
    public function run(): void
    {
        Integration::updateOrCreate(
            [
                'provider' => 'meta',
            ],
            [
                'name' => 'Meta',
                'category' => 'social',
                'is_active' => true,
            ]
        );
    }
}