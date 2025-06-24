<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\Dto\OrderDto;
use Illuminate\Support\Collection;

interface OrderServiceInterface
{
    public function getAllOrders(): Collection;

    public function createOrder(array $data): OrderDto;

    public function getOrderById(int $id): ?OrderDto;

    public function updateOrder(int $id, array $data): ?OrderDto;

    public function deleteOrder(int $id): bool;
}
