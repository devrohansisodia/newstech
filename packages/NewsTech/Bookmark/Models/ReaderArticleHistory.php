<?php

namespace NewsTech\Bookmark\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NewsTech\Article\Models\Article;
use NewsTech\Bookmark\Database\Factories\ReaderArticleHistoryFactory;
use NewsTech\Reader\Models\Reader;

class ReaderArticleHistory extends Model
{
    /** @use HasFactory<ReaderArticleHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'reader_id',
        'article_id',
        'last_viewed_at',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'reader_id' => 'integer',
            'article_id' => 'integer',
            'last_viewed_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    protected static function newFactory(): ReaderArticleHistoryFactory
    {
        return ReaderArticleHistoryFactory::new();
    }
}
