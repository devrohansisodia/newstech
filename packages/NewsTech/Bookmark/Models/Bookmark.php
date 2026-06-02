<?php

namespace NewsTech\Bookmark\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NewsTech\Article\Models\Article;
use NewsTech\Bookmark\Database\Factories\BookmarkFactory;
use NewsTech\Reader\Models\Reader;

class Bookmark extends Model
{
    /** @use HasFactory<BookmarkFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'reader_id',
        'article_id',
        'folder_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reader_id' => 'integer',
            'article_id' => 'integer',
            'folder_id' => 'integer',
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

    public function folder(): BelongsTo
    {
        return $this->belongsTo(BookmarkFolder::class, 'folder_id');
    }

    protected static function newFactory(): BookmarkFactory
    {
        return BookmarkFactory::new();
    }
}
