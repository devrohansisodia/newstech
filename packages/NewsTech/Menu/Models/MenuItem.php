<?php

namespace NewsTech\Menu\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NewsTech\Category\Models\Category;
use NewsTech\Menu\Database\Factories\MenuItemFactory;
use NewsTech\Page\Models\Page;

class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'menu_group_id',
        'parent_id',
        'type',
        'label',
        'url',
        'page_id',
        'category_id',
        'sort_order',
        'status',
        'opens_in_new_tab',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'sort_order' => 0,
        'status' => true,
        'opens_in_new_tab' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'menu_group_id' => 'integer',
            'parent_id' => 'integer',
            'page_id' => 'integer',
            'category_id' => 'integer',
            'sort_order' => 'integer',
            'status' => 'boolean',
            'opens_in_new_tab' => 'boolean',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            'custom_url' => 'Custom URL',
            'page' => 'Page',
            'category' => 'Category',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class, 'menu_group_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function statusLabel(): string
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function statusTone(): string
    {
        return $this->status ? 'success' : 'neutral';
    }

    protected static function newFactory(): MenuItemFactory
    {
        return MenuItemFactory::new();
    }
}
