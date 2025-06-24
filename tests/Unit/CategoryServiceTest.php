<?php

namespace Tests\Unit;

use App\Dto\CategoryDto;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = new CategoryService();
    }

    public function test_get_all_categories_returns_collection_of_dtos(): void
    {
        Category::factory()->count(3)->create();

        $result = $this->categoryService->getAllCategories();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(CategoryDto::class, $result);
    }

    public function test_get_all_categories_uses_cache(): void
    {
        Cache::shouldReceive('tags')
            ->with(['categories'])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(collect());

        $this->categoryService->getAllCategories();
    }

    public function test_create_category_returns_dto(): void
    {
        $data = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'is_active' => true
        ];

        $result = $this->categoryService->createCategory($data);

        $this->assertInstanceOf(CategoryDto::class, $result);
        $this->assertEquals('Test Category', $result->name);
        $this->assertEquals('Test Description', $result->description);
        $this->assertTrue($result->is_active);
        $this->assertDatabaseHas('categories', $data);
    }

    public function test_create_category_flushes_cache(): void
    {
        Cache::shouldReceive('tags')
            ->with(['categories'])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        $data = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'is_active' => true
        ];

        $this->categoryService->createCategory($data);
    }

    public function test_get_category_by_id_returns_dto_when_found(): void
    {
        $category = Category::factory()->create();

        $result = $this->categoryService->getCategoryById($category->id);

        $this->assertInstanceOf(CategoryDto::class, $result);
        $this->assertEquals($category->id, $result->id);
        $this->assertEquals($category->name, $result->name);
    }

    public function test_get_category_by_id_returns_null_when_not_found(): void
    {
        $result = $this->categoryService->getCategoryById(999);

        $this->assertNull($result);
    }

    public function test_get_category_by_id_uses_cache(): void
    {
        $categoryId = 1;
        $cacheKey = "category_{$categoryId}";

        Cache::shouldReceive('tags')
            ->with(['categories', $cacheKey])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('remember')
            ->with($cacheKey, \Mockery::any(), \Mockery::any())
            ->once()
            ->andReturn(null);

        $this->categoryService->getCategoryById($categoryId);
    }

    public function test_update_category_returns_dto_when_found(): void
    {
        $category = Category::factory()->create();
        $updateData = [
            'name' => 'Updated Name',
            'is_active' => false
        ];

        $result = $this->categoryService->updateCategory($category->id, $updateData);

        $this->assertInstanceOf(CategoryDto::class, $result);
        $this->assertEquals('Updated Name', $result->name);
        $this->assertFalse($result->is_active);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'is_active' => false
        ]);
    }

    public function test_update_category_returns_null_when_not_found(): void
    {
        $result = $this->categoryService->updateCategory(999, ['name' => 'Test']);

        $this->assertNull($result);
    }

    public function test_update_category_flushes_cache(): void
    {
        $category = Category::factory()->create();
        $cacheKey = "category_{$category->id}";

        Cache::shouldReceive('tags')
            ->with(['categories', $cacheKey])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        $this->categoryService->updateCategory($category->id, ['name' => 'Updated']);
    }

    public function test_delete_category_returns_true_when_found(): void
    {
        $category = Category::factory()->create();

        $result = $this->categoryService->deleteCategory($category->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_delete_category_returns_false_when_not_found(): void
    {
        $result = $this->categoryService->deleteCategory(999);

        $this->assertFalse($result);
    }

    public function test_delete_category_flushes_cache_when_successful(): void
    {
        $category = Category::factory()->create();
        $cacheKey = "category_{$category->id}";

        Cache::shouldReceive('tags')
            ->with(['categories', $cacheKey])
            ->once()
            ->andReturnSelf();
        
        Cache::shouldReceive('flush')
            ->once();

        $this->categoryService->deleteCategory($category->id);
    }
}