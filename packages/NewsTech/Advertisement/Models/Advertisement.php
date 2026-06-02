<?php

namespace NewsTech\Advertisement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use NewsTech\Advertisement\Database\Factories\AdvertisementFactory;

class Advertisement extends Model
{
    /** @use HasFactory<AdvertisementFactory> */
    use HasFactory;

    use SoftDeletes;

    public const TYPE_IMAGE = 'image';

    public const TYPE_HTML = 'html';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'status',
        'slot_key',
        'title',
        'image_path',
        'target_url',
        'html_content',
        'open_in_new_tab',
        'nofollow',
        'sponsored',
        'starts_at',
        'ends_at',
        'priority',
        'impressions_count',
        'clicks_count',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'nofollow' => 'boolean',
            'sponsored' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'priority' => 'integer',
            'impressions_count' => 'integer',
            'clicks_count' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_IMAGE => 'Image Banner',
            self::TYPE_HTML => 'HTML / Code',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->type] ?? ucfirst($this->type ?? 'Unknown');
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst($this->status ?? 'Unknown');
    }

    public function isRenderableAt(?Carbon $moment = null): bool
    {
        $moment ??= now();

        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function relAttributes(): string
    {
        $attributes = [];

        if ($this->nofollow) {
            $attributes[] = 'nofollow';
        }

        if ($this->sponsored) {
            $attributes[] = 'sponsored';
        }

        if ($this->open_in_new_tab) {
            $attributes[] = 'noopener';
            $attributes[] = 'noreferrer';
        }

        return implode(' ', array_unique($attributes));
    }

    protected static function newFactory(): AdvertisementFactory
    {
        return AdvertisementFactory::new();
    }
}
