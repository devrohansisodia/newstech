<?php

namespace NewsTech\Article\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use NewsTech\Article\Database\Factories\ArticleFactory;
use NewsTech\Author\Models\Author;
use NewsTech\Bookmark\Models\Bookmark;
use NewsTech\Bookmark\Models\ReaderArticleHistory;
use NewsTech\Category\Models\Category;
use NewsTech\Comment\Models\Comment;
use NewsTech\Core\Models\Concerns\HasSlugHelper;
use NewsTech\Core\Models\Concerns\HasStatusLabels;
use NewsTech\Core\Models\Concerns\ResolvesMediaUrls;
use NewsTech\Tag\Models\Tag;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    use HasSlugHelper;
    use HasStatusLabels;
    use ResolvesMediaUrls;
    use SoftDeletes;

    protected const STATUS_LABELS = [
        'draft' => 'Draft',
        'review' => 'In Review',
        'published' => 'Published',
        'scheduled' => 'Scheduled',
        'archived' => 'Archived',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'view_count',
        'status',
        'is_featured',
        'is_breaking',
        'published_at',
        'scheduled_at',
        'meta_title',
        'meta_description',
        'focus_keyword',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
        'view_count' => 0,
        'is_featured' => false,
        'is_breaking' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'author_id' => 'integer',
            'view_count' => 'integer',
            'is_featured' => 'boolean',
            'is_breaking' => 'boolean',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function readingHistory(): HasMany
    {
        return $this->hasMany(ReaderArticleHistory::class);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'scheduled' => 'warning',
            'review' => 'primary',
            'archived' => 'neutral',
            default => 'neutral',
        };
    }

    protected function featuredImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->resolveMediaUrl($this->featured_image)
        );
    }

    protected static function newFactory(): ArticleFactory
    {
        return ArticleFactory::new();
    }
}
