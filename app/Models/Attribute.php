<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_required',
        'is_filterable',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_attributes')
            ->withPivot('is_required', 'sort_order')
            ->withTimestamps();
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'variant_attributes')
            ->withPivot('attribute_value_id')
            ->withTimestamps();
    }
}

