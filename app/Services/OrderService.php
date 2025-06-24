<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\OrderDto;
use App\Models\Order;
use App\Models\Product;
use App\Services\Interfaces\OrderServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OrderService implements OrderServiceInterface
{
    public function getAllOrders(): Collection
    {
        return Cache::tags(['orders'])->remember('all_orders', now()->addHour(), function () {
            return Order::with('product')->get()->map(fn (Order $order) => OrderDto::fromModel($order));
        });
    }

    public function createOrder(array $data): OrderDto
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);

            $data['unit_price'] = $product->price;
            $data['total_price'] = $product->price * $data['quantity'];
            $data['order_date'] = $data['order_date'] ?? Carbon::now();

            $order = Order::create($data);

            $product->quantity -= $data['quantity'];
            $product->save();

            Cache::tags(['orders', "product_{$order->product_id}"])->flush();
            Cache::tags(["product_{$product->id}"])->flush();

            return OrderDto::fromModel($order->load('product'));
        });
    }

    public function getOrderById(int $id): ?OrderDto
    {
        $cacheKey = "order_{$id}";
        return Cache::tags(['orders', $cacheKey])->remember($cacheKey, now()->addHour(), function () use ($id) {
            $order = Order::with('product')->find($id);
            return $order ? OrderDto::fromModel($order) : null;
        });
    }

    public function updateOrder(int $id, array $data): ?OrderDto
    {
        return DB::transaction(function () use ($id, $data) {
            $order = Order::with('product')->find($id);
            if (!$order) {
                return null;
            }

            $allowedUpdates = [
                'customer_name' => $data['customer_name'] ?? $order->customer_name,
                'customer_email' => $data['customer_email'] ?? $order->customer_email,
                'status' => $data['status'] ?? $order->status,
            ];
            $order->update($allowedUpdates);

            Cache::tags(['orders', "order_{$id}", "product_{$order->product_id}"])->flush();

            return OrderDto::fromModel($order->fresh('product'));
        });
    }

    public function deleteOrder(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $order = Order::find($id);
            if (!$order) {
                return false;
            }

            $productId = $order->product_id;

            $deleted = $order->delete();
            if ($deleted) {
                Cache::tags(['orders', "order_{$id}", "product_{$productId}"])->flush();
            }
            return $deleted;
        });
    }
}
