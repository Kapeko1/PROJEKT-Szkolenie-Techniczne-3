<?php

namespace Tests\Unit;

use App\Dto\CategoryDto;
use App\Dto\OrderDto;
use App\Dto\ProductDto;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DtoTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_dto_from_model(): void
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'description' => 'Test Description',
            'is_active' => true
        ]);

        $dto = CategoryDto::fromModel($category);

        $this->assertInstanceOf(CategoryDto::class, $dto);
        $this->assertEquals($category->id, $dto->id);
        $this->assertEquals('Test Category', $dto->name);
        $this->assertEquals('Test Description', $dto->description);
        $this->assertTrue($dto->is_active);
        $this->assertEquals(0, $dto->products_count);
    }

    public function test_category_dto_from_array(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test Category',
            'description' => 'Test Description',
            'is_active' => true,
            'products_count' => 5
        ];

        $dto = CategoryDto::fromArray($data);

        $this->assertInstanceOf(CategoryDto::class, $dto);
        $this->assertEquals(1, $dto->id);
        $this->assertEquals('Test Category', $dto->name);
        $this->assertEquals('Test Description', $dto->description);
        $this->assertTrue($dto->is_active);
        $this->assertEquals(5, $dto->products_count);
    }

    public function test_category_dto_to_array(): void
    {
        $dto = new CategoryDto(
            id: 1,
            name: 'Test Category',
            description: 'Test Description',
            is_active: true,
            products_count: 3
        );

        $array = $dto->toArray();

        $expected = [
            'id' => 1,
            'name' => 'Test Category',
            'description' => 'Test Description',
            'is_active' => true,
            'products_count' => 3,
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_product_dto_from_model_with_category(): void
    {
        $category = Category::factory()->create(['name' => 'Electronics']);
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'quantity' => 10,
            'category_id' => $category->id,
            'is_active' => true
        ]);
        $product->load('category');

        $dto = ProductDto::fromModel($product);

        $this->assertInstanceOf(ProductDto::class, $dto);
        $this->assertEquals($product->id, $dto->id);
        $this->assertEquals('Test Product', $dto->name);
        $this->assertEquals('Test Description', $dto->description);
        $this->assertEquals('TEST-001', $dto->sku);
        $this->assertEquals(99.99, $dto->price);
        $this->assertEquals(10, $dto->quantity);
        $this->assertEquals($category->id, $dto->category_id);
        $this->assertEquals('Electronics', $dto->category_name);
        $this->assertTrue($dto->is_active);
    }

    public function test_product_dto_from_model_without_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Test Product'
        ]);

        $dto = ProductDto::fromModel($product);

        $this->assertNull($dto->category_name);
    }

    public function test_product_dto_from_array(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'quantity' => 10,
            'category_id' => 1,
            'category_name' => 'Electronics',
            'is_active' => true
        ];

        $dto = ProductDto::fromArray($data);

        $this->assertInstanceOf(ProductDto::class, $dto);
        $this->assertEquals(1, $dto->id);
        $this->assertEquals('Test Product', $dto->name);
        $this->assertEquals('Test Description', $dto->description);
        $this->assertEquals('TEST-001', $dto->sku);
        $this->assertEquals(99.99, $dto->price);
        $this->assertEquals(10, $dto->quantity);
        $this->assertEquals(1, $dto->category_id);
        $this->assertEquals('Electronics', $dto->category_name);
        $this->assertTrue($dto->is_active);
    }

    public function test_product_dto_to_array(): void
    {
        $dto = new ProductDto(
            id: 1,
            name: 'Test Product',
            description: 'Test Description',
            sku: 'TEST-001',
            price: 99.99,
            quantity: 10,
            category_id: 1,
            category_name: 'Electronics',
            is_active: true
        );

        $array = $dto->toArray();

        $expected = [
            'id' => 1,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'quantity' => 10,
            'category_id' => 1,
            'category_name' => 'Electronics',
            'is_active' => true,
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_order_dto_from_model(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'category_id' => $category->id
        ]);
        
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
        $order->load('product');

        $dto = OrderDto::fromModel($order);

        $this->assertInstanceOf(OrderDto::class, $dto);
        $this->assertEquals($order->id, $dto->id);
        $this->assertEquals($product->id, $dto->product_id);
        $this->assertEquals('Test Product', $dto->product_name);
        $this->assertEquals('John Doe', $dto->customer_name);
        $this->assertEquals('john@example.com', $dto->customer_email);
        $this->assertEquals(2, $dto->quantity);
        $this->assertEquals(50.00, $dto->unit_price);
        $this->assertEquals(100.00, $dto->total_price);
        $this->assertEquals('pending', $dto->status);
        $this->assertEquals($orderDate->toIso8601String(), $dto->order_date);
    }

    public function test_order_dto_from_array(): void
    {
        $data = [
            'id' => 1,
            'product_id' => 1,
            'product_name' => 'Test Product',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00,
            'status' => 'pending',
            'order_date' => '2024-01-01T10:00:00+00:00'
        ];

        $dto = OrderDto::fromArray($data);

        $this->assertInstanceOf(OrderDto::class, $dto);
        $this->assertEquals(1, $dto->id);
        $this->assertEquals(1, $dto->product_id);
        $this->assertEquals('Test Product', $dto->product_name);
        $this->assertEquals('John Doe', $dto->customer_name);
        $this->assertEquals('john@example.com', $dto->customer_email);
        $this->assertEquals(2, $dto->quantity);
        $this->assertEquals(50.00, $dto->unit_price);
        $this->assertEquals(100.00, $dto->total_price);
        $this->assertEquals('pending', $dto->status);
        $this->assertEquals('2024-01-01T10:00:00+00:00', $dto->order_date);
    }

    public function test_order_dto_to_array(): void
    {
        $dto = new OrderDto(
            id: 1,
            product_id: 1,
            product_name: 'Test Product',
            customer_name: 'John Doe',
            customer_email: 'john@example.com',
            quantity: 2,
            unit_price: 50.00,
            total_price: 100.00,
            status: 'pending',
            order_date: '2024-01-01T10:00:00+00:00'
        );

        $array = $dto->toArray();

        $expected = [
            'id' => 1,
            'product_id' => 1,
            'product_name' => 'Test Product',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00,
            'status' => 'pending',
            'order_date' => '2024-01-01T10:00:00+00:00',
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_product_dto_handles_null_values(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category_id' => 1,
        ];

        $dto = ProductDto::fromArray($data);

        $this->assertNull($dto->description);
        $this->assertEquals(0.0, $dto->price);
        $this->assertEquals(0, $dto->quantity);
        $this->assertNull($dto->category_name);
        $this->assertTrue($dto->is_active);
    }

    public function test_category_dto_handles_boolean_conversion(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test Category',
            'description' => 'Test Description',
            'is_active' => false,
            'products_count' => 0
        ];

        $dto = CategoryDto::fromArray($data);

        $this->assertFalse($dto->is_active);
    }
}