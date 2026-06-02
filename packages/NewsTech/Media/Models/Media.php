<?php

namespace NewsTech\Media\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NewsTech\Core\Support\MediaManager;
use NewsTech\Media\Database\Factories\MediaFactory;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'disk',
        'path',
        'filename',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'alt_text',
        'caption',
        'uploaded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'uploaded_by' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function isImage(): bool
    {
        return is_string($this->mime_type) && str_starts_with($this->mime_type, 'image/');
    }

    public function resolvedUrl(): string
    {
        return app(MediaManager::class)->url($this->path, $this->disk);
    }

    protected static function newFactory(): MediaFactory
    {
        return MediaFactory::new();
    }
}
