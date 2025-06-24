<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->randomFloat(2, 10, 500);
        $totalPrice = $quantity * $unitPrice;

        return [
            'product_id' => Product::factory(),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'order_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Set specific quantity and recalculate total price.
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(function (array $attributes) use ($quantity) {
            $unitPrice = $attributes['unit_price'] ?? $this->faker->randomFloat(2, 10, 500);
            return [
                'quantity' => $quantity,
                'total_price' => $quantity * $unitPrice,
            ];
        });
    }
}