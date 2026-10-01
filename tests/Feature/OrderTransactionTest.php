<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class OrderTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_rolls_back_when_inventory_is_insufficient(): void
    {
        $product = Product::factory()->create([
            'price' => 1000,
        ]);

        $warehouse = Warehouse::factory()->create();

        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'available_quantity' => 2,
            'reserved_quantity' => 0,
        ]);

        $service = app(OrderService::class);

        try {
            $service->create(
                $product,
                $inventory,
                3
            );

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Insufficient inventory.',
                $e->getMessage()
            );
        }

        $this->assertDatabaseCount('orders', 0);

        $this->assertDatabaseCount('order_items', 0);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'available_quantity' => 2,
            'reserved_quantity' => 0,
        ]);
    }
}
