<?php

namespace App\Services;

use App\Models\Inventory;
use App\Services\InventoryService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function create(Product $product, Inventory $inventory, int $quantity): Order
    {
        return DB::transaction(function () use (
            $product,
            $inventory,
            $quantity
        ) {
            $subtotal = $product->price * $quantity;

            $order = Order::create([
                'user_id' => null,
                'order_number' => 'ORD-' . now()->format('YmdHis'),
                'total_amount' => $subtotal,
                'status' => 'pending_payment',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ]);

            $reservedInventory = $this->inventoryService->reserve(
                $inventory,
                $quantity
            );

            if ($reservedInventory === null) {
                throw new RuntimeException('Insufficient inventory.');
            }

            return $order;
        });
    }
}
