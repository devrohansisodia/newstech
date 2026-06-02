<?php

namespace NewsTech\Author\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NewsTech\Article\Models\Article;
use NewsTech\Author\Database\Factories\AuthorFactory;
use NewsTech\Core\Models\Concerns\HasSlugHelper;
use NewsTech\Core\Models\Concerns\ResolvesMediaUrls;

class Author extends Model
{
    /** @use HasFactory<AuthorFactory> */
    use HasFactory;

    use HasSlugHelper;
    use ResolvesMediaUrls;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'designation',
        'bio',
        'avatar',
        'facebook_url',
        'twitter_url',
        'linkedin_url',
        'website_url',
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

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->resolveMediaUrl($this->avatar)
        );
    }

    protected static function newFactory(): AuthorFactory
    {
        return AuthorFactory::new();
    }
}
