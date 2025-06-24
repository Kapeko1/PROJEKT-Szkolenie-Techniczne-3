<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\Order;
use Illuminate\Support\Carbon;

class OrderDto
{
    public function __construct(
        public readonly int $id,
        public readonly int $product_id,
        public readonly string $product_name,
        public readonly string $customer_name,
        public readonly string $customer_email,
        public readonly int $quantity,
        public readonly float $unit_price,
        public readonly float $total_price,
        public readonly string $status,
        public readonly string $order_date
    ) {
    }

    public static function fromModel(Order $order): self
    {
        return new self(
            id: $order->id,
            product_id: $order->product_id,
            product_name: $order->product->name,
            customer_name: $order->customer_name,
            customer_email: $order->customer_email,
            quantity: $order->quantity,
            unit_price: (float) $order->unit_price,
            total_price: (float) $order->total_price,
            status: $order->status,
            order_date: $order->order_date->toIso8601String()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            product_id: $data['product_id'],
            product_name: $data['product_name'] ?? null,
            customer_name: $data['customer_name'],
            customer_email: $data['customer_email'],
            quantity: (int) $data['quantity'],
            unit_price: (float) $data['unit_price'],
            total_price: (float) $data['total_price'],
            status: $data['status'],
            order_date: $data['order_date']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'order_date' => $this->order_date,
        ];
    }
}
