<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'price',
        'discount_price',
        'stock_quantity',
        'in_stock',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'in_stock' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($variant) {
            if (empty($variant->sku)) {
                $variant->sku = 'VAR-' . strtoupper(Str::random(8));
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'variant_attributes', 'variant_id', 'attribute_id')
            ->withPivot('attribute_value_id')
            ->withTimestamps();
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attributes', 'variant_id', 'attribute_value_id')
            ->withTimestamps();
    }

    public function getFinalPriceAttribute()
    {
        if ($this->price) {
            return $this->discount_price ?? $this->price;
        }
        return $this->product->final_price;
    }

    public function getDisplayNameAttribute()
    {
        $attributes = $this->attributeValues->map(function ($value) {
            return $value->display_value ?? $value->value;
        })->implode(' / ');

        return $this->product->name . ($attributes ? ' - ' . $attributes : '');
    }
}

