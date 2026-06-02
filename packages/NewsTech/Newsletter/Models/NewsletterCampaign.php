<?php

namespace NewsTech\Newsletter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsletterCampaign extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'subject',
        'preheader',
        'content',
        'status',
        'scheduled_at',
        'sent_at',
        'recipients_count',
        'delivered_count',
        'failed_count',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'recipients_count' => 'integer',
            'delivered_count' => 'integer',
            'failed_count' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_SENDING => 'Sending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? str($this->status)->headline()->toString();
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED], true);
    }

    public function canSend(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED], true) && $this->sent_at === null;
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(NewsletterCampaignRecipient::class, 'campaign_id');
    }
}
