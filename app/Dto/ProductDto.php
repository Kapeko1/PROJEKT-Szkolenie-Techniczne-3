<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\Product;

class ProductDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $sku,
        public readonly float $price,
        public readonly int $quantity,
        public readonly int $category_id,
        public readonly ?string $category_name,
        public readonly bool $is_active
    ) {
    }

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            description: $product->description,
            sku: $product->sku,
            price: (float) $product->price,
            quantity: $product->quantity,
            category_id: $product->category_id,
            category_name: $product->relationLoaded('category') ? $product->category->name : null,
            is_active: $product->is_active
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            sku: $data['sku'],
            price: (float) ($data['price'] ?? 0.0),
            quantity: (int) ($data['quantity'] ?? 0),
            category_id: $data['category_id'],
            category_name: $data['category_name'] ?? null,
            is_active: (bool) ($data['is_active'] ?? true)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,
            'is_active' => $this->is_active,
        ];
    }
}
