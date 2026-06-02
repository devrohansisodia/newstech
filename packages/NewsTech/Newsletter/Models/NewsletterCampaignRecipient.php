<?php

namespace NewsTech\Newsletter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterCampaignRecipient extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'email',
        'status',
        'sent_at',
        'failed_at',
        'failure_reason',
        'opened_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'campaign_id' => 'integer',
            'subscriber_id' => 'integer',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'opened_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(NewsletterCampaign::class, 'campaign_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscriber::class, 'subscriber_id');
    }

    public function statusLabel(): string
    {
        return str($this->status)->headline()->toString();
    }
}
