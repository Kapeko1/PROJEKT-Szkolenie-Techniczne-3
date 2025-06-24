<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_orders_returns_success(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Order::factory()->count(3)->create(['product_id' => $product->id]);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'product_id',
                    'product_name',
                    'customer_name',
                    'customer_email',
                    'quantity',
                    'unit_price',
                    'total_price',
                    'status',
                    'order_date'
                ]
            ]);
    }

    public function test_create_order_with_valid_data(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'quantity' => 20
        ]);

        $orderData = [
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 2,
            'status' => 'pending',
            'order_date' => '2024-01-15 10:30:00'
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'product_id',
                'product_name',
                'customer_name',
                'customer_email',
                'quantity',
                'unit_price',
                'total_price',
                'status',
                'order_date'
            ])
            ->assertJson([
                'product_id' => $product->id,
                'customer_name' => 'John Doe',
                'customer_email' => 'john@example.com',
                'quantity' => 2,
                'unit_price' => 100.00,
                'total_price' => 200.00,
                'status' => 'pending'
            ]);

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

    public function test_create_order_without_order_date_uses_current_time(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50.00,
            'quantity' => 10
        ]);

        $orderData = [
            'product_id' => $product->id,
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
            'quantity' => 1,
            'status' => 'pending'
        ];

        $beforeTime = Carbon::now()->subSecond();
        $response = $this->postJson('/api/orders', $orderData);
        $afterTime = Carbon::now()->addSecond();

        $response->assertStatus(201);
        
        $order = Order::first();
        $this->assertTrue($order->order_date->between($beforeTime, $afterTime));
    }

    public function test_create_order_reduces_product_quantity(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'quantity' => 15
        ]);

        $orderData = [
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 5,
            'status' => 'pending'
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201);
        
        $product->refresh();
        $this->assertEquals(10, $product->quantity);
    }

    public function test_get_order_by_id_returns_success(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'category_id' => $category->id
        ]);
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'customer_name' => 'Alice Johnson',
            'status' => 'completed'
        ]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'product_id',
                'product_name',
                'customer_name',
                'customer_email',
                'quantity',
                'unit_price',
                'total_price',
                'status',
                'order_date'
            ])
            ->assertJson([
                'id' => $order->id,
                'product_id' => $product->id,
                'product_name' => 'Test Product',
                'customer_name' => 'Alice Johnson',
                'status' => 'completed'
            ]);
    }

    public function test_get_order_by_invalid_id_returns_not_found(): void
    {
        $response = $this->getJson('/api/orders/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Order not found'
            ]);
    }

    public function test_update_order_with_valid_data(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'customer_name' => 'Old Name',
            'status' => 'pending'
        ]);

        $updateData = [
            'customer_name' => 'Updated Name',
            'customer_email' => 'updated@example.com',
            'status' => 'completed'
        ];

        $response = $this->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $order->id,
                'customer_name' => 'Updated Name',
                'customer_email' => 'updated@example.com',
                'status' => 'completed'
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_name' => 'Updated Name',
            'customer_email' => 'updated@example.com',
            'status' => 'completed'
        ]);
    }

    public function test_update_order_ignores_non_allowed_fields(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00
        ]);

        $updateData = [
            'quantity' => 5,
            'unit_price' => 75.00,
            'total_price' => 375.00,
            'status' => 'completed'
        ];

        $response = $this->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'completed'
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00,
            'status' => 'completed'
        ]);
    }

    public function test_update_nonexistent_order_returns_not_found(): void
    {
        $updateData = [
            'status' => 'completed'
        ];

        $response = $this->putJson('/api/orders/999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Order not found'
            ]);
    }

    public function test_delete_order_returns_success(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create(['product_id' => $product->id]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_delete_nonexistent_order_returns_not_found(): void
    {
        $response = $this->deleteJson('/api/orders/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Order not found'
            ]);
    }

    public function test_orders_include_product_name(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Gaming Laptop',
            'category_id' => $category->id
        ]);
        $order = Order::factory()->create(['product_id' => $product->id]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'product_id' => $product->id,
                'product_name' => 'Gaming Laptop'
            ]);
    }

    public function test_create_order_with_nonexistent_product_fails(): void
    {
        $orderData = [
            'product_id' => 999,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 2,
            'status' => 'pending'
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(400);
    }

    public function test_order_calculations_are_correct(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 25.50,
            'quantity' => 100
        ]);

        $orderData = [
            'product_id' => $product->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'quantity' => 3,
            'status' => 'pending'
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJson([
                'unit_price' => 25.50,
                'total_price' => 76.50,
                'quantity' => 3
            ]);
    }

    public function test_order_date_format_in_response(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $orderDate = Carbon::create(2024, 1, 15, 10, 30, 0);
        
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'order_date' => $orderDate
        ]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
        
        $responseData = $response->json();
        $this->assertStringContainsString('2024-01-15T10:30:00', $responseData['order_date']);
    }
}