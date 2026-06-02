<?php

namespace NewsTech\Comment\Support;

class CommentSpamCheckResult
{
    public function __construct(
        public bool $accepted,
        public bool $storeComment,
        public string $status,
        public bool $isSpam,
        public ?string $spamReason,
        public ?string $publicError,
        public ?string $successMessage,
    ) {}

    public static function approved(string $message): self
    {
        return new self(true, true, 'approved', false, null, null, $message);
    }

    public static function pending(string $message): self
    {
        return new self(true, true, 'pending', false, null, null, $message);
    }

    public static function spam(string $status, string $reason, string $message): self
    {
        return new self(false, true, $status, true, $reason, $message, null);
    }

    public static function stealthSpam(string $status, string $reason, string $message): self
    {
        return new self(true, true, $status, true, $reason, null, $message);
    }

    public static function blocked(string $message): self
    {
        return new self(false, false, 'rejected', false, null, $message, null);
    }
}
