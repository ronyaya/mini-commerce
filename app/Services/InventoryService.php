<?php

namespace App\Services;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function adjust(Inventory $inventory, int $quantity): ?Inventory
    {
        $updatedRows = Inventory::query()
            ->where('id', $inventory->id)
            ->whereRaw('available_quantity + ? >= 0', [$quantity])
            ->update([
                'available_quantity' => DB::raw(
                    "available_quantity + {$quantity}"
                ),
            ]);

        if ($updatedRows === 0) {
            return null;
        }

        return $inventory->refresh();
    }

    public function reserve(Inventory $inventory, int $quantity): ?Inventory
    {
        $updatedRows = Inventory::query()
            ->where('id', $inventory->id)
            ->where('available_quantity', '>=', $quantity)
            ->update([
                'available_quantity' => DB::raw(
                    "available_quantity - {$quantity}"
                ),
                'reserved_quantity' => DB::raw(
                    "reserved_quantity + {$quantity}"
                ),
            ]);

        if ($updatedRows === 0) {
            return null;
        }

        return $inventory->refresh();
    }

    public function reserveWithLock(
        int $inventoryId,
        int $quantity
    ): Inventory {
        return DB::transaction(function () use ($inventoryId, $quantity) {
            $inventory = Inventory::query()
                ->where('id', $inventoryId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($inventory->available_quantity < $quantity) {
                throw new RuntimeException('Insufficient inventory.');
            }

            $inventory->available_quantity -= $quantity;
            $inventory->reserved_quantity += $quantity;

            $inventory->save();

            return $inventory;
        });
    }
}
