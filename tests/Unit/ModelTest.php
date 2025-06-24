<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_model_attributes(): void
    {
        $category = Category::factory()->create([
            'name' => 'Electronics',
            'description' => 'Electronic devices',
            'is_active' => true
        ]);

        $this->assertEquals('Electronics', $category->name);
        $this->assertEquals('Electronic devices', $category->description);
        $this->assertTrue($category->is_active);
        $this->assertInstanceOf(Carbon::class, $category->created_at);
        $this->assertInstanceOf(Carbon::class, $category->updated_at);
    }

    public function test_category_fillable_attributes(): void
    {
        $fillableAttributes = [
            'name',
            'description',
            'is_active'
        ];

        $category = new Category();
        $this->assertEquals($fillableAttributes, $category->getFillable());
    }

    public function test_category_casts_is_active_to_boolean(): void
    {
        $category = Category::factory()->create(['is_active' => '1']);
        $this->assertIsBool($category->is_active);
        $this->assertTrue($category->is_active);

        $category = Category::factory()->create(['is_active' => '0']);
        $this->assertIsBool($category->is_active);
        $this->assertFalse($category->is_active);
    }

    public function test_category_has_many_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->products);
        $this->assertInstanceOf(Product::class, $category->products->first());
    }

    public function test_category_cache_methods(): void
    {
        $category = Category::factory()->create();

        $this->assertEquals("category_{$category->id}", $category->getCacheKey());
        $this->assertEquals(['categories'], $category->getCacheTags());
    }

    public function test_product_model_attributes(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'iPhone 15',
            'description' => 'Latest smartphone',
            'sku' => 'IPHONE15-001',
            'price' => 999.99,
            'quantity' => 50,
            'category_id' => $category->id,
            'is_active' => true
        ]);

        $this->assertEquals('iPhone 15', $product->name);
        $this->assertEquals('Latest smartphone', $product->description);
        $this->assertEquals('IPHONE15-001', $product->sku);
        $this->assertEquals(999.99, (float) $product->price);
        $this->assertEquals(50, $product->quantity);
        $this->assertEquals($category->id, $product->category_id);
        $this->assertTrue($product->is_active);
    }

    public function test_product_fillable_attributes(): void
    {
        $fillableAttributes = [
            'name',
            'description',
            'sku',
            'price',
            'quantity',
            'category_id',
            'is_active'
        ];

        $product = new Product();
        $this->assertEquals($fillableAttributes, $product->getFillable());
    }

    public function test_product_casts(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'price' => '99.99',
            'quantity' => '10',
            'is_active' => '1',
            'category_id' => $category->id
        ]);

        $this->assertIsString($product->price);
        $this->assertIsInt($product->quantity);
        $this->assertIsBool($product->is_active);
        $this->assertInstanceOf(Carbon::class, $product->created_at);
        $this->assertInstanceOf(Carbon::class, $product->updated_at);
    }

    public function test_product_belongs_to_category(): void
    {
        $category = Category::factory()->create(['name' => 'Electronics']);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals('Electronics', $product->category->name);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_product_has_many_orders(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Order::factory()->count(2)->create(['product_id' => $product->id]);

        $this->assertCount(2, $product->orders);
        $this->assertInstanceOf(Order::class, $product->orders->first());
    }

    public function test_product_cache_methods(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertEquals("product_{$product->id}", $product->getCacheKey());
        $this->assertEquals(['products', "category_{$category->id}"], $product->getCacheTags());
    }

    public function test_order_model_attributes(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $orderDate = Carbon::now();
        
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00,
            'status' => 'pending',
            'order_date' => $orderDate
        ]);

        $this->assertEquals($product->id, $order->product_id);
        $this->assertEquals('John Doe', $order->customer_name);
        $this->assertEquals('john@example.com', $order->customer_email);
        $this->assertEquals(2, $order->quantity);
        $this->assertEquals(50.00, (float) $order->unit_price);
        $this->assertEquals(100.00, (float) $order->total_price);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals($orderDate->format('Y-m-d H:i:s'), $order->order_date->format('Y-m-d H:i:s'));
    }

    public function test_order_fillable_attributes(): void
    {
        $fillableAttributes = [
            'product_id',
            'customer_name',
            'customer_email',
            'quantity',
            'unit_price',
            'total_price',
            'status',
            'order_date'
        ];

        $order = new Order();
        $this->assertEquals($fillableAttributes, $order->getFillable());
    }

    public function test_order_casts(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'quantity' => '3',
            'unit_price' => '25.50',
            'total_price' => '76.50',
            'order_date' => '2024-01-15 10:30:00'
        ]);

        $this->assertIsInt($order->quantity);
        $this->assertIsString($order->unit_price);
        $this->assertIsString($order->total_price);
        $this->assertInstanceOf(Carbon::class, $order->order_date);
        $this->assertInstanceOf(Carbon::class, $order->created_at);
        $this->assertInstanceOf(Carbon::class, $order->updated_at);
    }

    public function test_order_belongs_to_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'category_id' => $category->id
        ]);
        $order = Order::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $order->product);
        $this->assertEquals('Test Product', $order->product->name);
        $this->assertEquals($product->id, $order->product->id);
    }

    public function test_order_cache_methods(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create(['product_id' => $product->id]);

        $this->assertEquals("order_{$order->id}", $order->getCacheKey());
        $this->assertEquals(['orders', "product_{$product->id}"], $order->getCacheTags());
    }

    public function test_category_product_relationship_cascade(): void
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->fresh()->products);
        $this->assertTrue($category->products->contains($product1));
        $this->assertTrue($category->products->contains($product2));
    }

    public function test_product_order_relationship_cascade(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order1 = Order::factory()->create(['product_id' => $product->id]);
        $order2 = Order::factory()->create(['product_id' => $product->id]);

        $this->assertCount(2, $product->fresh()->orders);
        $this->assertTrue($product->orders->contains($order1));
        $this->assertTrue($product->orders->contains($order2));
    }

    public function test_models_implement_cacheable_interface(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(\App\Interfaces\CacheableInterface::class, $category);
        $this->assertInstanceOf(\App\Interfaces\CacheableInterface::class, $product);
        $this->assertInstanceOf(\App\Interfaces\CacheableInterface::class, $order);
    }

    public function test_decimal_precision_for_prices(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'price' => 99.999,
            'category_id' => $category->id
        ]);
        
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'unit_price' => 99.999,
            'total_price' => 199.998
        ]);

        $this->assertEquals('99.999', $product->getRawOriginal('price'));
        $this->assertEquals('99.999', $order->getRawOriginal('unit_price'));
        $this->assertEquals('199.998', $order->getRawOriginal('total_price'));
    }
}