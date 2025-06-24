<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Interfaces\CacheableInterface;

class Order extends Model implements CacheableInterface
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'customer_name',
        'customer_email',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'order_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'order_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getCacheKey(): string
    {
        return "order_{$this->id}";
    }

    public function getCacheTags(): array
    {
        return ['orders', "product_{$this->product_id}"];
    }

}
