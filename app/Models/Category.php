<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'hub_route_slug',
        'description',
        'image',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'category_attributes')
            ->withPivot('is_required', 'sort_order')
            ->withTimestamps()
            ->orderBy('category_attributes.sort_order');
    }

    /**
     * Attributes for this category, or inherited from parent (e.g. "Shoes" parent).
     */
    public function applicableAttributes()
    {
        $attributes = $this->attributes()->with('values')->get();

        if ($attributes->isEmpty() && $this->parent_id) {
            $this->loadMissing('parent');
            if ($this->parent) {
                return $this->parent->attributes()->with('values')->orderBy('category_attributes.sort_order')->get();
            }
        }

        return $attributes;
    }

    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }
}

