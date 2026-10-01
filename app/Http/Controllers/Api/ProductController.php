<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {

        Log::info('request test');
        $products = Product::paginate(10);

        return ProductResource::collection($products);
    }

    public function show(int $id, ProductService $productService): ProductResource
    {
        /*
        $product = Product::findOrFail($id);

        //return response()->json($product);
        return new ProductResource($product);
        */

        $product = $productService->find($id);

        return new ProductResource($product);
    }

    public function store(StoreProductRequest $request, ProductService $productService)
    {
        /*
        $product = Product::create(
            $request->validated()
        );

        return response()->json($product, 201);
        */

        $product = $productService->create(
            $request->validated()
        );

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, int $id, ProductService $productService): ProductResource
    {
        $product = Product::findOrFail($id);

        /*
        $product->update(
            $request->validated()
        );
        return response()->json($product);
        */
        $product = $productService->update(
            $product,
            $request->validated()
        );

        return new ProductResource($product);
    }

    public function destroy(int $id, ProductService $productService): JsonResponse
    {
        $product = Product::findOrFail($id);

        $productService->delete($product);

        return response()->json(null, 204);
    }
}
