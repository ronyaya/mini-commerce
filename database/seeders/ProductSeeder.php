<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::firstOrCreate(
            [
                'sku' => 'IPHONE17PRO-256-BLK-SEED',
            ],
            [
                'name' => 'iPhone 17 Pro',
                'price' => 39900,
                'status' => 'active',
            ]
        );

        Product::factory()
            ->count(10)
            ->create();
    }
}
