<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Interfaces\CacheableInterface;

class Product extends Model implements CacheableInterface
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'quantity',
        'category_id',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getCacheKey(): string
    {
        return "product_{$this->id}";
    }

    public function getCacheTags(): array
    {
        return ['products', "category_{$this->category_id}"];
    }

}
