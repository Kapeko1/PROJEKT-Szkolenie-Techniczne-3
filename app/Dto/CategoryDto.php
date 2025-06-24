<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\Category;

class CategoryDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $is_active,
        public readonly ?int $products_count = null
    ) {
    }

    public static function fromModel(Category $category): self
    {
        return new self(
            id: $category->id,
            name: $category->name,
            description: $category->description,
            is_active: $category->is_active,
            products_count: $category->products->count()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'],
            is_active: (bool) ($data['is_active']),
            products_count: $data['products_count']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'products_count' => $this->products_count,
        ];
    }
}
