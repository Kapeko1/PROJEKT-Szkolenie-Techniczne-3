<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\Dto\CategoryDto;
use Illuminate\Support\Collection;

interface CategoryServiceInterface
{
    public function getAllCategories(): Collection;

    public function createCategory(array $data): CategoryDto;

    public function getCategoryById(int $id): ?CategoryDto;

    public function updateCategory(int $id, array $data): ?CategoryDto;

    public function deleteCategory(int $id): bool;
}
