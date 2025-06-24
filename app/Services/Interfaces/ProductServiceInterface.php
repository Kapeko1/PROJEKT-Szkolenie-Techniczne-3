<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\Dto\ProductDto;
use Illuminate\Support\Collection;

interface ProductServiceInterface
{
    public function getAllProducts(): Collection;

    public function createProduct(array $data): ProductDto;

    public function getProductById(int $id): ?ProductDto;

    public function updateProduct(int $id, array $data): ?ProductDto;

    public function deleteProduct(int $id): bool;
}
