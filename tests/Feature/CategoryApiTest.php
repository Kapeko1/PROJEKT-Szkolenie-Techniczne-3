<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_categories_returns_success(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'is_active',
                    'products_count'
                ]
            ]);
    }

    public function test_create_category_with_valid_data(): void
    {
        $categoryData = [
            'name' => 'Electronics',
            'description' => 'Electronic devices and accessories',
            'is_active' => true
        ];

        $response = $this->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'is_active',
                'products_count'
            ])
            ->assertJson([
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
                'is_active' => true,
                'products_count' => 0
            ]);

        $this->assertDatabaseHas('categories', $categoryData);
    }

    public function test_create_category_with_minimal_data(): void
    {
        $categoryData = [
            'name' => 'Books',
            'is_active' => true
        ];

        $response = $this->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Books',
                'is_active' => true
            ]);

        $this->assertDatabaseHas('categories', $categoryData);
    }

    public function test_get_category_by_id_returns_success(): void
    {
        $category = Category::factory()->create([
            'name' => 'Electronics',
            'description' => 'Electronic devices'
        ]);

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'is_active',
                'products_count'
            ])
            ->assertJson([
                'id' => $category->id,
                'name' => 'Electronics',
                'description' => 'Electronic devices'
            ]);
    }

    public function test_get_category_by_invalid_id_returns_not_found(): void
    {
        $response = $this->getJson('/api/categories/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Category not found'
            ]);
    }

    public function test_update_category_with_valid_data(): void
    {
        $category = Category::factory()->create([
            'name' => 'Electronics',
            'is_active' => true
        ]);

        $updateData = [
            'name' => 'Updated Electronics',
            'is_active' => false
        ];

        $response = $this->putJson("/api/categories/{$category->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $category->id,
                'name' => 'Updated Electronics',
                'is_active' => false
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Electronics',
            'is_active' => false
        ]);
    }

    public function test_update_nonexistent_category_returns_not_found(): void
    {
        $updateData = [
            'name' => 'Updated Category'
        ];

        $response = $this->putJson('/api/categories/999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Category not found'
            ]);
    }

    public function test_delete_category_returns_success(): void
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_delete_nonexistent_category_returns_not_found(): void
    {
        $response = $this->deleteJson('/api/categories/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Category not found'
            ]);
    }

    public function test_category_with_products_shows_products_count(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'products_count' => 5
            ]);
    }

    public function test_get_all_categories_includes_products_count(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Product::factory()->count(3)->create(['category_id' => $category1->id]);
        Product::factory()->count(2)->create(['category_id' => $category2->id]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);

        $categories = $response->json();
        $this->assertCount(2, $categories);

        $category1Data = collect($categories)->firstWhere('id', $category1->id);
        $category2Data = collect($categories)->firstWhere('id', $category2->id);

        $this->assertEquals(3, $category1Data['products_count']);
        $this->assertEquals(2, $category2Data['products_count']);
    }

    public function test_api_requires_json_headers(): void
    {
        $response = $this->post('/api/categories', [
            'name' => 'Test Category',
            'is_active' => true
        ]);

        $response->assertStatus(201);
    }

    public function test_api_with_proper_json_headers(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->postJson('/api/categories', [
            'name' => 'Test Category',
            'is_active' => true
        ]);

        $response->assertStatus(201);
    }
}
