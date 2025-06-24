<?php

namespace Tests\Unit;

use App\Dto\ProductDto;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService();
    }

    public function test_get_all_products_returns_collection_of_dtos(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $result = $this->productService->getAllProducts();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(ProductDto::class, $result);
    }

    public function test_get_all_products_uses_cache(): void
    {
        Cache::shouldReceive('tags')
            ->with(['products'])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(collect());

        $this->productService->getAllProducts();
    }

    public function test_create_product_returns_dto(): void
    {
        $category = Category::factory()->create();
        $data = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'quantity' => 10,
            'category_id' => $category->id,
            'is_active' => true
        ];

        $result = $this->productService->createProduct($data);

        $this->assertInstanceOf(ProductDto::class, $result);
        $this->assertEquals('Test Product', $result->name);
        $this->assertEquals('TEST-001', $result->sku);
        $this->assertEquals(99.99, $result->price);
        $this->assertEquals(10, $result->quantity);
        $this->assertTrue($result->is_active);
        $this->assertDatabaseHas('products', $data);
    }

    public function test_create_product_flushes_cache(): void
    {
        $category = Category::factory()->create();
        
        Cache::shouldReceive('tags')
            ->with(['products', "category_{$category->id}"])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        $data = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'quantity' => 10,
            'category_id' => $category->id,
            'is_active' => true
        ];

        $this->productService->createProduct($data);
    }

    public function test_get_product_by_id_returns_dto_when_found(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $result = $this->productService->getProductById($product->id);

        $this->assertInstanceOf(ProductDto::class, $result);
        $this->assertEquals($product->id, $result->id);
        $this->assertEquals($product->name, $result->name);
        $this->assertEquals($product->sku, $result->sku);
    }

    public function test_get_product_by_id_returns_null_when_not_found(): void
    {
        $result = $this->productService->getProductById(999);

        $this->assertNull($result);
    }

    public function test_get_product_by_id_uses_cache(): void
    {
        $productId = 1;
        $cacheKey = "product_{$productId}";

        Cache::shouldReceive('tags')
            ->with(['products', $cacheKey])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('remember')
            ->with($cacheKey, \Mockery::any(), \Mockery::any())
            ->once()
            ->andReturn(null);

        $this->productService->getProductById($productId);
    }

    public function test_update_product_returns_dto_when_found(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $updateData = [
            'name' => 'Updated Product',
            'price' => 149.99,
            'quantity' => 5
        ];

        $result = $this->productService->updateProduct($product->id, $updateData);

        $this->assertInstanceOf(ProductDto::class, $result);
        $this->assertEquals('Updated Product', $result->name);
        $this->assertEquals(149.99, $result->price);
        $this->assertEquals(5, $result->quantity);
        $this->assertDatabaseHas('products', array_merge(['id' => $product->id], $updateData));
    }

    public function test_update_product_returns_null_when_not_found(): void
    {
        $result = $this->productService->updateProduct(999, ['name' => 'Test']);

        $this->assertNull($result);
    }

    public function test_update_product_flushes_cache(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        Cache::shouldReceive('tags')
            ->with(['products', "product_{$product->id}", "category_{$category->id}"])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        $this->productService->updateProduct($product->id, ['name' => 'Updated']);
    }

    public function test_update_product_flushes_old_and_new_category_cache_when_category_changes(): void
    {
        $oldCategory = Category::factory()->create();
        $newCategory = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $oldCategory->id]);

        Cache::shouldReceive('tags')
            ->with(['products', "product_{$product->id}", "category_{$oldCategory->id}"])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        Cache::shouldReceive('tags')
            ->with(["category_{$newCategory->id}"])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        $this->productService->updateProduct($product->id, ['category_id' => $newCategory->id]);
    }

    public function test_delete_product_returns_true_when_found(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $result = $this->productService->deleteProduct($product->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_delete_product_returns_false_when_not_found(): void
    {
        $result = $this->productService->deleteProduct(999);

        $this->assertFalse($result);
    }

    public function test_delete_product_flushes_cache_when_successful(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        Cache::shouldReceive('tags')
            ->with(['products', "product_{$product->id}", "category_{$category->id}"])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        $this->productService->deleteProduct($product->id);
    }
}