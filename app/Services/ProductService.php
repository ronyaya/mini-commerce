<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;


class ProductService
{
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        $cacheKey = "product:{$product->id}";

        $invalidated = false;

        for ($i = 1; $i <= 3; $i++) {
            try {
                Redis::del($cacheKey);

                $invalidated = true;

                break;
            } catch (\Throwable $e) {
                logger()->warning('PRODUCT CACHE INVALIDATION RETRY', [
                    'product_id' => $product->id,
                    'attempt' => $i,
                    'error' => $e->getMessage(),
                ]);

                usleep(200000);
            }
        }

        if (!$invalidated) {
            logger()->error('PRODUCT CACHE INVALIDATION FAILED', [
                'product_id' => $product->id,
            ]);
        }

        return $product;
    }

    public function find(int $id): Product
    {
        $cacheKey = "product:{$id}";
        $nullMarker = '__NULL__';

        $cached = Redis::get($cacheKey);

        if ($cached !== null) {

            logger()->info('PRODUCT CACHE HIT', [
                'product_id' => $id,
            ]);

            if ($cached === $nullMarker) {
                throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())
                    ->setModel(Product::class, [$id]);
            }

            $data = json_decode($cached, true);

            $product = new Product();
            $product->setRawAttributes($data);

            return $product;
        }

        logger()->info('PRODUCT CACHE MISS', [
            'product_id' => $id,
        ]);

        $lock = Cache::lock("lock:product:{$id}", 5);

        if ($lock->get()) {
            try {
                logger()->info('PRODUCT LOCK ACQUIRED', [
                    'product_id' => $id,
                ]);

                // Double Check
                $cached = Redis::get($cacheKey);

                if ($cached !== null) {
                    if ($cached === $nullMarker) {
                        throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())
                            ->setModel(Product::class, [$id]);
                    }

                    $data = json_decode($cached, true);

                    $product = new Product();
                    $product->setRawAttributes($data);

                    return $product;
                }

                // 只有拿到 Lock 的 Request 才能來這裡
                logger()->info('QUERY PRODUCT FROM DB', [
                    'product_id' => $id,
                ]);

                $product = Product::find($id);

                if (!$product) {
                    Redis::setex(
                        $cacheKey,
                        300,
                        $nullMarker
                    );

                    throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())
                        ->setModel(Product::class, [$id]);
                }

                Redis::setex(
                    $cacheKey,
                    300,
                    json_encode([
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'status' => $product->status,
                    ])
                );

                return $product;
            } finally {
                $lock->release();
            }
        }

        logger()->info('PRODUCT LOCK WAIT', [
            'product_id' => $id,
        ]);

        for ($i = 0; $i < 20; $i++) {
            usleep(200000);

            $cached = Redis::get($cacheKey);

            if ($cached !== null) {
                if ($cached === $nullMarker) {
                    throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())
                        ->setModel(Product::class, [$id]);
                }

                $data = json_decode($cached, true);

                $product = new Product();
                $product->setRawAttributes($data);

                return $product;
            }
        }

        throw new \RuntimeException(
            'Product cache rebuild timeout.'
        );
    }

    public function delete(Product $product): void
    {
        $id = $product->id;

        $product->delete();

        Redis::del("product:{$id}");
    }
}
