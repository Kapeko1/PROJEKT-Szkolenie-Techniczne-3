<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_products_returns_success(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'sku',
                    'price',
                    'quantity',
                    'category_id',
                    'category_name',
                    'is_active'
                ]
            ]);
    }

    public function test_create_product_with_valid_data(): void
    {
        $category = Category::factory()->create();
        $productData = [
            'name' => 'iPhone 15',
            'description' => 'Latest Apple smartphone',
            'sku' => 'IPHONE15-001',
            'price' => 999.99,
            'quantity' => 50,
            'category_id' => $category->id,
            'is_active' => true
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'sku',
                'price',
                'quantity',
                'category_id',
                'category_name',
                'is_active'
            ])
            ->assertJson([
                'name' => 'iPhone 15',
                'description' => 'Latest Apple smartphone',
                'sku' => 'IPHONE15-001',
                'price' => 999.99,
                'quantity' => 50,
                'category_id' => $category->id,
                'is_active' => true
            ]);

        $this->assertDatabaseHas('products', $productData);
    }

    public function test_create_product_with_minimal_data(): void
    {
        $category = Category::factory()->create();
        $productData = [
            'name' => 'Simple Product',
            'sku' => 'SIMPLE-001',
            'price' => 19.99,
            'quantity' => 10,
            'category_id' => $category->id,
            'is_active' => true
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Simple Product',
                'sku' => 'SIMPLE-001',
                'price' => 19.99,
                'quantity' => 10
            ]);

        $this->assertDatabaseHas('products', $productData);
    }

    public function test_get_product_by_id_returns_success(): void
    {
        $category = Category::factory()->create(['name' => 'Electronics']);
        $product = Product::factory()->create([
            'name' => 'MacBook Pro',
            'sku' => 'MBP-001',
            'category_id' => $category->id
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'sku',
                'price',
                'quantity',
                'category_id',
                'category_name',
                'is_active'
            ])
            ->assertJson([
                'id' => $product->id,
                'name' => 'MacBook Pro',
                'sku' => 'MBP-001',
                'category_id' => $category->id,
                'category_name' => 'Electronics'
            ]);
    }

    public function test_get_product_by_invalid_id_returns_not_found(): void
    {
        $response = $this->getJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found'
            ]);
    }

    public function test_update_product_with_valid_data(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Old Product',
            'price' => 100.00,
            'quantity' => 5,
            'category_id' => $category->id
        ]);

        $updateData = [
            'name' => 'Updated Product',
            'price' => 149.99,
            'quantity' => 10
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => 'Updated Product',
                'price' => 149.99,
                'quantity' => 10
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 149.99,
            'quantity' => 10
        ]);
    }

    public function test_update_nonexistent_product_returns_not_found(): void
    {
        $updateData = [
            'name' => 'Updated Product'
        ];

        $response = $this->putJson('/api/products/999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found'
            ]);
    }

    public function test_delete_product_returns_success(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_delete_nonexistent_product_returns_not_found(): void
    {
        $response = $this->deleteJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found'
            ]);
    }

    public function test_products_include_category_name(): void
    {
        $category = Category::factory()->create(['name' => 'Smartphones']);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'category_id' => $category->id,
                'category_name' => 'Smartphones'
            ]);
    }

    public function test_get_all_products_includes_category_names(): void
    {
        $category1 = Category::factory()->create(['name' => 'Electronics']);
        $category2 = Category::factory()->create(['name' => 'Books']);
        
        Product::factory()->create(['category_id' => $category1->id, 'name' => 'Laptop']);
        Product::factory()->create(['category_id' => $category2->id, 'name' => 'Novel']);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        
        $products = $response->json();
        $this->assertCount(2, $products);
        
        $laptop = collect($products)->firstWhere('name', 'Laptop');
        $novel = collect($products)->firstWhere('name', 'Novel');
        
        $this->assertEquals('Electronics', $laptop['category_name']);
        $this->assertEquals('Books', $novel['category_name']);
    }

    public function test_create_product_with_nonexistent_category_fails(): void
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'quantity' => 10,
            'category_id' => 999,
            'is_active' => true
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(500);
    }

    public function test_product_price_is_numeric(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'price' => 99.99,
            'category_id' => $category->id
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $productData = $response->json();
        
        $this->assertIsFloat($productData['price']);
        $this->assertEquals(99.99, $productData['price']);
    }

    public function test_product_quantity_is_integer(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'quantity' => 42,
            'category_id' => $category->id
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $productData = $response->json();
        
        $this->assertIsInt($productData['quantity']);
        $this->assertEquals(42, $productData['quantity']);
    }
}