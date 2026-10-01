<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_reserve_inventory(): void
    {
        $product = Product::factory()->create();

        $warehouse = Warehouse::factory()->create();

        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'available_quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $service = app(InventoryService::class);

        $result = $service->reserve($inventory, 3);

        $this->assertNotNull($result);

        $this->assertSame(7, $result->available_quantity);
        $this->assertSame(3, $result->reserved_quantity);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'available_quantity' => 7,
            'reserved_quantity' => 3,
        ]);
    }

    public function test_cannot_reserve_when_inventory_is_insufficient(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'available_quantity' => 2,
            'reserved_quantity' => 0,
        ]);

        $service = app(InventoryService::class);

        $result = $service->reserve($inventory, 3);

        $inventory->refresh();

        $this->assertNull($result);
        $this->assertSame(2, $inventory->available_quantity);
        $this->assertSame(0, $inventory->reserved_quantity);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'available_quantity' => 2,
            'reserved_quantity' => 0,
        ]);
    }
}
