<?php

namespace NewsTech\Menu\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NewsTech\Menu\Database\Factories\MenuGroupFactory;

class MenuGroup extends Model
{
    /** @use HasFactory<MenuGroupFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'location',
        'status',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public static function locationOptions(): array
    {
        return [
            'header' => 'Header',
            'footer' => 'Footer',
            'mobile' => 'Mobile',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function statusLabel(): string
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function statusTone(): string
    {
        return $this->status ? 'success' : 'neutral';
    }

    public function locationLabel(): string
    {
        return self::locationOptions()[$this->location] ?? ucfirst($this->location);
    }

    protected static function newFactory(): MenuGroupFactory
    {
        return MenuGroupFactory::new();
    }
}
