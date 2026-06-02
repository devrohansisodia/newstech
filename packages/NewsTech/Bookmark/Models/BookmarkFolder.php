<?php

namespace NewsTech\Bookmark\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NewsTech\Bookmark\Database\Factories\BookmarkFolderFactory;
use NewsTech\Reader\Models\Reader;

class BookmarkFolder extends Model
{
    /** @use HasFactory<BookmarkFolderFactory> */
    use HasFactory;

    protected $fillable = [
        'reader_id',
        'name',
        'slug',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'reader_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class, 'folder_id');
    }

    protected static function newFactory(): BookmarkFolderFactory
    {
        return BookmarkFolderFactory::new();
    }
}
