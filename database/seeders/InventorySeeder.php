<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::where(
            'sku',
            'IPHONE17PRO-256-BLK-SEED'
        )->firstOrFail();

        $warehouse = Warehouse::where(
            'code',
            'TPE'
        )->firstOrFail();

        Inventory::firstOrCreate(
            [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
            ],
            [
                'available_quantity' => 10,
                'reserved_quantity' => 0,
            ]
        );
    }
}
