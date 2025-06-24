<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\ProductDto;
use App\Models\Product;
use App\Services\Interfaces\ProductServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductService implements ProductServiceInterface
{
    public function getAllProducts(): Collection
    {
        return Cache::tags(['products'])->remember('all_products', now()->addHour(), function () {
            return Product::with('category')->get()->map(fn (Product $product) => ProductDto::fromModel($product));
        });
    }

    public function createProduct(array $data): ProductDto
    {
        $product = Product::create($data);
        Cache::tags(['products', "category_{$product->category_id}"])->flush();
        return ProductDto::fromModel($product->load('category'));
    }

    public function getProductById(int $id): ?ProductDto
    {
        $cacheKey = "product_{$id}";
        return Cache::tags(['products', $cacheKey])->remember($cacheKey, now()->addHour(), function () use ($id) {
            $product = Product::with('category')->find($id);
            return $product ? ProductDto::fromModel($product) : null;
        });
    }

    public function updateProduct(int $id, array $data): ?ProductDto
    {
        $product = Product::find($id);
        if (!$product) {
            return null;
        }
        $oldCategoryId = $product->category_id;
        $product->update($data);

        Cache::tags(['products', "product_{$id}", "category_{$oldCategoryId}"])->flush();
        if (isset($data['category_id']) && $oldCategoryId !== $data['category_id']) {
            Cache::tags(["category_{$data['category_id']}"])->flush();
        }

        return ProductDto::fromModel($product->fresh('category'));
    }

    public function deleteProduct(int $id): bool
    {
        $product = Product::find($id);
        if (!$product) {
            return false;
        }
        $deleted = $product->delete();
        if ($deleted) {
            Cache::tags(['products', "product_{$id}", "category_{$product->category_id}"])->flush();
        }
        return $deleted;
    }
}
