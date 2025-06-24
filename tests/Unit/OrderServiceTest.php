<?php

namespace Tests\Unit;

use App\Dto\OrderDto;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
    }

    public function test_get_all_orders_returns_collection_of_dtos(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Order::factory()->count(3)->create(['product_id' => $product->id]);

        $result = $this->orderService->getAllOrders();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(OrderDto::class, $result);
    }

    public function test_get_all_orders_uses_cache(): void
    {
        Cache::shouldReceive('tags')
            ->with(['orders'])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(collect());

        $this->orderService->getAllOrders();
    }

    public function test_create_order_returns_dto(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'quantity' => 10
        ]);
        
        $data = [
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 2,
            'status' => 'pending'
        ];

        $result = $this->orderService->createOrder($data);

        $this->assertInstanceOf(OrderDto::class, $result);
        $this->assertEquals('John Doe', $result->customer_name);
        $this->assertEquals('john@example.com', $result->customer_email);
        $this->assertEquals(2, $result->quantity);
        $this->assertEquals(100.00, $result->unit_price);
        $this->assertEquals(200.00, $result->total_price);
        $this->assertEquals('pending', $result->status);
        
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00,
            'status' => 'pending'
        ]);
    }

    public function test_create_order_reduces_product_quantity(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'quantity' => 10
        ]);
        
        $data = [
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 3,
            'status' => 'pending'
        ];

        $this->orderService->createOrder($data);

        $product->refresh();
        $this->assertEquals(7, $product->quantity);
    }

    public function test_create_order_flushes_cache(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'quantity' => 10
        ]);

        Cache::shouldReceive('tags')
            ->with(['orders', "product_{$product->id}"])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        Cache::shouldReceive('tags')
            ->with(["product_{$product->id}"])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        $data = [
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 2,
            'status' => 'pending'
        ];

        $this->orderService->createOrder($data);
    }

    public function test_get_order_by_id_returns_dto_when_found(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create(['product_id' => $product->id]);

        $result = $this->orderService->getOrderById($order->id);

        $this->assertInstanceOf(OrderDto::class, $result);
        $this->assertEquals($order->id, $result->id);
        $this->assertEquals($order->customer_name, $result->customer_name);
        $this->assertEquals($order->customer_email, $result->customer_email);
    }

    public function test_get_order_by_id_returns_null_when_not_found(): void
    {
        $result = $this->orderService->getOrderById(999);

        $this->assertNull($result);
    }

    public function test_get_order_by_id_uses_cache(): void
    {
        $orderId = 1;
        $cacheKey = "order_{$orderId}";

        Cache::shouldReceive('tags')
            ->with(['orders', $cacheKey])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('remember')
            ->with($cacheKey, \Mockery::any(), \Mockery::any())
            ->once()
            ->andReturn(null);

        $this->orderService->getOrderById($orderId);
    }

    public function test_update_order_returns_dto_when_found(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'status' => 'pending'
        ]);
        
        $updateData = [
            'customer_name' => 'Jane Doe',
            'status' => 'completed'
        ];

        $result = $this->orderService->updateOrder($order->id, $updateData);

        $this->assertInstanceOf(OrderDto::class, $result);
        $this->assertEquals('Jane Doe', $result->customer_name);
        $this->assertEquals('completed', $result->status);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_name' => 'Jane Doe',
            'status' => 'completed'
        ]);
    }

    public function test_update_order_returns_null_when_not_found(): void
    {
        $result = $this->orderService->updateOrder(999, ['status' => 'completed']);

        $this->assertNull($result);
    }

    public function test_update_order_flushes_cache(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create(['product_id' => $product->id]);

        Cache::shouldReceive('tags')
            ->with(['orders', "order_{$order->id}", "product_{$product->id}"])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        $this->orderService->updateOrder($order->id, ['status' => 'completed']);
    }

    public function test_delete_order_returns_true_when_found(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create(['product_id' => $product->id]);

        $result = $this->orderService->deleteOrder($order->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_delete_order_returns_false_when_not_found(): void
    {
        $result = $this->orderService->deleteOrder(999);

        $this->assertFalse($result);
    }

    public function test_delete_order_flushes_cache_when_successful(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create(['product_id' => $product->id]);

        Cache::shouldReceive('tags')
            ->with(['orders', "order_{$order->id}", "product_{$product->id}"])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        $this->orderService->deleteOrder($order->id);
    }

    public function test_create_order_uses_database_transaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'quantity' => 10
        ]);
        
        $data = [
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 2,
            'status' => 'pending'
        ];

        $this->orderService->createOrder($data);
    }

    public function test_update_order_uses_database_transaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create(['product_id' => $product->id]);

        $this->orderService->updateOrder($order->id, ['status' => 'completed']);
    }

    public function test_delete_order_uses_database_transaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create(['product_id' => $product->id]);

        $this->orderService->deleteOrder($order->id);
    }
}