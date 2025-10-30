<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'parent_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('sort_order');
    }

    /**
     * Get the products in the category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all descendants (children, grandchildren, etc.).
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors (parent, grandparent, etc.).
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    /**
     * Get the full path of the category.
     */
    public function getFullPathAttribute(): string
    {
        $ancestors = $this->ancestors()->reverse();
        $path = $ancestors->pluck('name')->push($this->name);

        return $path->implode(' > ');
    }

    /**
     * Get the total number of products in this category and its subcategories.
     */
    public function getTotalProductsAttribute(): int
    {
        $productCount = $this->products()->active()->count();

        foreach ($this->children as $child) {
            $productCount += $child->getTotalProductsAttribute();
        }

        return $productCount;
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include child categories.
     */
    public function scopeChild($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope a query to search categories by name or description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Get categories as a hierarchical tree structure.
     */
    public static function getTree()
    {
        return static::with(['children' => function ($query) {
            $query->active()->orderBy('sort_order');
        }])
        ->active()
        ->root()
        ->orderBy('sort_order')
        ->get();
    }

    /**
     * Get categories as a flat list with hierarchy indicators.
     */
    public static function getFlatList()
    {
        $categories = collect();

        $roots = static::active()->root()->orderBy('sort_order')->get();

        foreach ($roots as $root) {
            $categories->push($root);
            $categories = $categories->merge(static::getFlatListWithChildren($root, 1));
        }

        return $categories;
    }

    /**
     * Helper method to get flat list with children.
     */
    private static function getFlatListWithChildren($category, $level)
    {
        $categories = collect();

        foreach ($category->children()->active()->orderBy('sort_order')->get() as $child) {
            $child->level = $level;
            $categories->push($child);
            $categories = $categories->merge(static::getFlatListWithChildren($child, $level + 1));
        }

        return $categories;
    }
}