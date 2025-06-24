<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\CategoryDto;
use App\Models\Category;
use App\Services\Interfaces\CategoryServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService implements CategoryServiceInterface
{
    public function getAllCategories(): Collection
    {
        return Cache::tags(['categories'])->remember('all_categories', now()->addHour(), function () {
            return Category::withCount('products')->get()->map(fn (Category $category) => CategoryDto::fromModel($category));
        });
    }

    public function createCategory(array $data): CategoryDto
    {
        $category = Category::create($data);
        Cache::tags(['categories'])->flush();
        return CategoryDto::fromModel($category);
    }

    public function getCategoryById(int $id): ?CategoryDto
    {
        $cacheKey = "category_{$id}";
        return Cache::tags(['categories', $cacheKey])->remember($cacheKey, now()->addHour(), function () use ($id) {
            $category = Category::withCount('products')->find($id);
            return $category ? CategoryDto::fromModel($category) : null;
        });
    }

    public function updateCategory(int $id, array $data): ?CategoryDto
    {
        $category = Category::find($id);
        if (!$category) {
            return null;
        }
        $category->update($data);
        Cache::tags(['categories', "category_{$id}"])->flush();
        return CategoryDto::fromModel($category->fresh(['products']));
    }

    public function deleteCategory(int $id): bool
    {
        $category = Category::find($id);
        if (!$category) {
            return false;
        }
        $deleted = $category->delete();
        if ($deleted) {
            Cache::tags(['categories', "category_{$id}"])->flush();
        }
        return $deleted;
    }
}
