<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class OrderController extends Controller
{
    public function store(OrderService $orderService): JsonResponse
    {
        $product = Product::findOrFail(5);

        $inventory = Inventory::where('product_id', 5)
            ->firstOrFail();

        try {
            $order = $orderService->create(
                $product,
                $inventory,
                2
            );

            return response()->json([
                'order_id' => $order->id,
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
