<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::firstOrCreate(
            ['code' => 'TPE'],
            ['name' => '台北倉庫']
        );

        Warehouse::firstOrCreate(
            ['code' => 'TXG'],
            ['name' => '台中倉庫']
        );

        Warehouse::firstOrCreate(
            ['code' => 'KHH'],
            ['name' => '高雄倉庫']
        );
    }
}
