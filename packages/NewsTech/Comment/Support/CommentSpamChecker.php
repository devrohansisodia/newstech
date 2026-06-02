<?php

namespace NewsTech\Comment\Support;

use NewsTech\Article\Models\Article;
use NewsTech\Comment\Repositories\CommentRepository;
use NewsTech\Reader\Models\Reader;

class CommentSpamChecker
{
    public function __construct(protected CommentRepository $comments) {}

    /**
     * @param  array{name:string,email:string,website:?string,content:string,ip_address:?string,user_agent:?string,honeypot:string,reader:?Reader}  $payload
     */
    public function evaluate(Article $article, array $payload): CommentSpamCheckResult
    {
        if (! config('newstech-comment.enabled', true)) {
            return CommentSpamCheckResult::blocked('Comments are currently closed.');
        }

        if (! config('newstech-comment.guest_comments_enabled', true) && ! $payload['reader']) {
            return CommentSpamCheckResult::blocked('Guest comments are currently disabled.');
        }

        if ($payload['honeypot'] !== '') {
            return $this->stealthSpamResult('honeypot');
        }

        $content = trim($payload['content']);
        $email = mb_strtolower(trim($payload['email']));
        $ipAddress = $payload['ip_address'] ? trim($payload['ip_address']) : null;

        if ($this->exceedsLinkLimit($content)) {
            return $this->spamResult('too_many_links');
        }

        if ($this->containsBlockedWord($content)) {
            return $this->spamResult('blocked_word');
        }

        if ($this->matchesBlockedEmail($email)) {
            return $this->spamResult('blocked_email');
        }

        if ($ipAddress && $this->matchesBlockedIp($ipAddress)) {
            return $this->spamResult('blocked_ip');
        }

        if ($this->isThrottled($article, $email, $ipAddress)) {
            return CommentSpamCheckResult::blocked('Please wait before submitting another comment.');
        }

        if (config('newstech-comment.require_moderation', true)) {
            return CommentSpamCheckResult::pending('Your comment has been submitted and is awaiting moderation.');
        }

        return CommentSpamCheckResult::approved('Your comment has been published successfully.');
    }

    protected function exceedsLinkLimit(string $content): bool
    {
        $maxLinks = max(0, (int) config('newstech-comment.max_links_per_comment', 2));

        if ($maxLinks === 0) {
            return $this->countLinks($content) > 0;
        }

        return $this->countLinks($content) > $maxLinks;
    }

    protected function countLinks(string $content): int
    {
        preg_match_all('/https?:\/\/|www\./iu', $content, $matches);

        return count($matches[0]);
    }

    protected function containsBlockedWord(string $content): bool
    {
        $normalizedContent = mb_strtolower($content);

        foreach ($this->configuredList('newstech-comment.blocked_words') as $blockedWord) {
            if (str_contains($normalizedContent, $blockedWord)) {
                return true;
            }
        }

        return false;
    }

    protected function matchesBlockedEmail(string $email): bool
    {
        $domain = str_contains($email, '@')
            ? (string) str($email)->after('@')
            : '';

        foreach ($this->configuredList('newstech-comment.blocked_emails') as $blockedValue) {
            $normalizedBlockedValue = ltrim($blockedValue, '@');

            if ($blockedValue === $email || $normalizedBlockedValue === $domain) {
                return true;
            }
        }

        return false;
    }

    protected function matchesBlockedIp(string $ipAddress): bool
    {
        return in_array($ipAddress, $this->configuredList('newstech-comment.blocked_ips'), true);
    }

    protected function isThrottled(Article $article, string $email, ?string $ipAddress): bool
    {
        $throttleSeconds = max(0, (int) config('newstech-comment.throttle_seconds', 60));

        if ($throttleSeconds === 0) {
            return false;
        }

        return $this->comments->hasRecentSubmission(
            $article,
            $email,
            $ipAddress,
            $throttleSeconds
        );
    }

    /**
     * @return list<string>
     */
    protected function configuredList(string $key): array
    {
        $rawValue = (string) config($key, '');

        if ($rawValue === '') {
            return [];
        }

        $normalizedValue = str_replace(["\r\n", "\r"], "\n", $rawValue);
        $items = preg_split('/[\n,]+/', $normalizedValue) ?: [];

        return collect($items)
            ->map(fn (string $item): string => mb_strtolower(trim($item)))
            ->filter()
            ->values()
            ->all();
    }

    protected function spamResult(string $reason): CommentSpamCheckResult
    {
        $status = config('newstech-comment.auto_reject_spam', false) ? 'rejected' : 'pending';

        return CommentSpamCheckResult::spam(
            $status,
            $reason,
            'Your comment could not be accepted. Please review it and try again.'
        );
    }

    protected function stealthSpamResult(string $reason): CommentSpamCheckResult
    {
        $status = config('newstech-comment.auto_reject_spam', false) ? 'rejected' : 'pending';

        return CommentSpamCheckResult::stealthSpam(
            $status,
            $reason,
            'Your comment has been submitted and is awaiting moderation.'
        );
    }
}
