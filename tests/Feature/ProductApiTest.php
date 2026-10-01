<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_product_list(): void
    {
        Product::factory()->create([
            'name' => 'iPhone 17 Pro',
            'sku' => 'IPHONE17PRO-256-BLK',
            'price' => 39900,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => 'iPhone 17 Pro',
            'sku' => 'IPHONE17PRO-256-BLK',
        ]);
    }

    public function test_cannot_create_product_with_duplicate_sku(): void
    {
        Product::factory()->create([
            'sku' => 'IPHONE17PRO-256-BLK',
        ]);

        $response = $this->postJson('/api/products', [
            'name' => 'Another Product',
            'sku' => 'IPHONE17PRO-256-BLK',
            'price' => 1000,
            'status' => 'active',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'sku',
        ]);

        $this->assertDatabaseCount('products', 1);
    }

    public function test_cannot_create_product_with_invalid_price(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Bad Product',
            'sku' => 'BAD-001',
            'price' => -100,
            'status' => 'active',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'price',
        ]);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_cannot_create_product_when_name_is_missing(): void
    {
        $response = $this->postJson('/api/products', [
            'sku' => 'TEST-001',
            'price' => 1000,
            'status' => 'active',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
        ]);

        $this->assertDatabaseCount('products', 0);
    }
}
