<?php

namespace NewsTech\Newsletter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NewsTech\Newsletter\Database\Factories\NewsletterSubscriberFactory;

class NewsletterSubscriber extends Model
{
    /** @use HasFactory<NewsletterSubscriberFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'unsubscribe_token',
        'status',
        'source',
        'ip_address',
        'user_agent',
        'subscribed_at',
        'unsubscribed_at',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function statusLabel(): string
    {
        return str($this->status)->headline()->toString();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->unsubscribed_at === null;
    }

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(NewsletterCampaignRecipient::class, 'subscriber_id');
    }

    protected static function newFactory(): NewsletterSubscriberFactory
    {
        return NewsletterSubscriberFactory::new();
    }
}
