<?php

namespace NewsTech\Tag\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use NewsTech\Article\Models\Article;
use NewsTech\Core\Models\Concerns\HasSlugHelper;
use NewsTech\Tag\Database\Factories\TagFactory;

class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    use HasSlugHelper;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
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

    public function statusLabel(): string
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class)->withTimestamps();
    }

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }
}
