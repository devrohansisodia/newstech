<?php

namespace NewsTech\Reader\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NewsTech\Article\Models\Article;
use NewsTech\Bookmark\Models\Bookmark;
use NewsTech\Bookmark\Models\BookmarkFolder;
use NewsTech\Bookmark\Models\ReaderArticleHistory;
use NewsTech\Comment\Models\Comment;
use NewsTech\Reader\Database\Factories\ReaderFactory;
use NewsTech\Reader\Notifications\ReaderResetPasswordNotification;
use NewsTech\Reader\Notifications\ReaderVerifyEmailNotification;

class Reader extends Authenticatable implements MustVerifyEmail
{
    use CanResetPassword;

    /** @use HasFactory<ReaderFactory> */
    use HasFactory;
    use MustVerifyEmailTrait;
    use Notifiable;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'avatar',
        'bio',
        'website',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookmarkFolders(): HasMany
    {
        return $this->hasMany(BookmarkFolder::class);
    }

    public function readingHistory(): HasMany
    {
        return $this->hasMany(ReaderArticleHistory::class);
    }

    public function bookmarkedArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'bookmarks')->withTimestamps();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ReaderResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new ReaderVerifyEmailNotification);
    }

    protected static function newFactory(): ReaderFactory
    {
        return ReaderFactory::new();
    }
}
