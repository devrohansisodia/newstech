<?php

namespace NewsTech\Comment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use NewsTech\Admin\Models\AdminUser;
use NewsTech\Article\Models\Article;
use NewsTech\Comment\Database\Factories\CommentFactory;
use NewsTech\Core\Models\Concerns\HasStatusLabels;
use NewsTech\Reader\Models\Reader;

class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    use HasStatusLabels;
    use SoftDeletes;

    protected const STATUS_LABELS = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'article_id',
        'reader_id',
        'parent_id',
        'name',
        'email',
        'website',
        'content',
        'status',
        'is_spam',
        'spam_reason',
        'ip_address',
        'user_agent',
        'approved_at',
        'moderated_at',
        'moderated_by',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'article_id' => 'integer',
            'reader_id' => 'integer',
            'parent_id' => 'integer',
            'is_spam' => 'boolean',
            'approved_at' => 'datetime',
            'moderated_at' => 'datetime',
            'moderated_by' => 'integer',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'moderated_by');
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }
}
