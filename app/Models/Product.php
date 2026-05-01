<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    const TYPE_FINISHED = 'finished';
    const TYPE_SEMI_FINISHED = 'semi_finished';
    const TYPE_RAW_MATERIAL = 'raw_material';

    const KIND_REGULAR = 'regular';
    const KIND_WEIGHT = 'weight';
    const KIND_UNLIMITED = 'unlimited';
    const KIND_SERVICE = 'service';
    const KIND_BUNDLE = 'bundle';
    const KIND_FORMULA = 'formula';

    protected $fillable = [
        'category_id', 'name', 'sku', 'barcode', 'description',
        'price', 'cost_price', 'stock', 'min_stock', 'unit', 'image', 'is_active',
        'product_type', 'product_kind', 'meta',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    protected $appends = [
        'kind_label',
        'is_stockless',
    ];

    public static function typeLabels(): array
    {
        return [
            self::TYPE_FINISHED => 'Produk Jadi',
            self::TYPE_SEMI_FINISHED => 'Setengah Jadi',
            self::TYPE_RAW_MATERIAL => 'Bahan Baku',
        ];
    }

    public static function kindLabels(): array
    {
        return [
            self::KIND_REGULAR => 'Biasa',
            self::KIND_WEIGHT => 'Timbangan',
            self::KIND_UNLIMITED => 'Unlimited',
            self::KIND_SERVICE => 'Jasa',
            self::KIND_BUNDLE => 'Bundle',
            self::KIND_FORMULA => 'Formula',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function posGroups()
    {
        return $this->belongsToMany(PosGroup::class, 'pos_group_product')
                    ->withPivot('position')
                    ->orderBy('pos_group_product.position');
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function stockMutations()
    {
        return $this->hasMany(StockMutation::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->min_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function getTypeLabel(): string
    {
        return self::typeLabels()[$this->product_type] ?? $this->product_type;
    }

    public function getKindLabelAttribute(): string
    {
        return self::kindLabels()[$this->product_kind] ?? 'Biasa';
    }

    public function isStockless(): bool
    {
        return in_array($this->product_kind, [self::KIND_UNLIMITED, self::KIND_SERVICE]);
    }

    public function getIsStocklessAttribute(): bool
    {
        return $this->isStockless();
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/no-product.png');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('product_type', $type);
    }
}
