<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustInventoryRequest;
use App\Http\Requests\ReserveInventoryRequest;
use App\Models\Product;
use App\Models\Inventory;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function show(Product $product): JsonResponse
    {
        $availableQuantity = $product->inventories()
            ->sum('available_quantity');

        return response()->json([
            'product_id' => $product->id,
            'available_quantity' => $availableQuantity,
        ]);
    }

    public function adjust(AdjustInventoryRequest $request, Inventory $inventory, InventoryService $inventoryService): JsonResponse
    {
        $inventory = $inventoryService->adjust(
            $inventory,
            $request->validated('quantity')
        );

        if ($inventory === null) {
            return response()->json([
                'message' => 'Inventory adjustment would result in negative stock.',
            ], 422);
        }

        return response()->json([
            'id' => $inventory->id,
            'available_quantity' => $inventory->available_quantity,
            'reserved_quantity' => $inventory->reserved_quantity,
        ]);
    }

    public function reserve(
        ReserveInventoryRequest $request,
        Inventory $inventory,
        InventoryService $inventoryService
    ): JsonResponse {
        $inventory = $inventoryService->reserve(
            $inventory,
            $request->validated('quantity')
        );

        if ($inventory === null) {
            return response()->json([
                'message' => 'Insufficient inventory.',
            ], 422);
        }

        return response()->json([
            'id' => $inventory->id,
            'available_quantity' => $inventory->available_quantity,
            'reserved_quantity' => $inventory->reserved_quantity,
        ]);
    }

    public function reserveWithLock(
        ReserveInventoryRequest $request,
        Inventory $inventory,
        InventoryService $inventoryService
    ): JsonResponse {
        try {
            $inventory = $inventoryService->reserveWithLock(
                $inventory->id,
                $request->validated('quantity')
            );

            return response()->json([
                'id' => $inventory->id,
                'available_quantity' => $inventory->available_quantity,
                'reserved_quantity' => $inventory->reserved_quantity,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
