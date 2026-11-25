<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'type',
        'summary',
        'description',
        'terms',
        'price',
        'currency',
        'supplier_cost',
        'supplier',
        'supplier_product_id',
        'supplier_adapter',
        'meta',
        'image_path',
        'status',
        'is_active',
        'is_featured',
        'sort_order',
        'stock_quantity',
        'min_order',
        'max_order',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'price' => 'decimal:2',
            'supplier_cost' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name).'-'.Str::lower(Str::random(6));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isAvailable(): bool
    {
        return $this->is_active
            && $this->status === 'active'
            && ($this->stock_quantity === null || $this->stock_quantity > 0);
    }

    public function isActiveForPurchase(): bool
    {
        return $this->isAvailable();
    }

    public function estimatedDelivery(): ?string
    {
        $meta = $this->meta ?? [];
        $sources = [
            (string) ($meta['start_time'] ?? ''),
            (string) ($meta['supplier_service_name'] ?? ''),
            (string) ($meta['service_name'] ?? ''),
            (string) ($this->description ?? ''),
        ];

        foreach ($sources as $source) {
            if ($source === '') {
                continue;
            }

            if (preg_match('/start\s*time\s*[:\-]?\s*([^\]\[\n,]+)/i', $source, $matches)) {
                $value = trim((string) $matches[1]);
                if ($value !== '') {
                    return Str::limit($value, 60, '');
                }
            }
        }

        return null;
    }

    public function pricingUnit(): string
    {
        return (string) (($this->meta['pricing_unit'] ?? null) ?: 'per_unit');
    }

    public function isPer1000(): bool
    {
        return $this->pricingUnit() === 'per_1000';
    }

    public function priceDecimals(): int
    {
        return $this->isPer1000() ? 2 : 0;
    }

    public function priceSuffix(): string
    {
        return $this->isPer1000() ? ' / 1,000' : '';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
