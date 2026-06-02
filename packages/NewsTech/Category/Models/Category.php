<?php

namespace NewsTech\Category\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NewsTech\Article\Models\Article;
use NewsTech\Category\Database\Factories\CategoryFactory;
use NewsTech\Core\Models\Concerns\HasSlugHelper;
use NewsTech\Core\Models\Concerns\HasSortableOrder;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasSlugHelper;
    use HasSortableOrder;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'status',
        'sort_order',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function primaryArticles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class)->withTimestamps();
    }

    public function statusLabel(): string
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function statusTone(): string
    {
        return $this->status ? 'success' : 'neutral';
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
