<?php

namespace NewsTech\Comment\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use NewsTech\Article\Models\Article;
use NewsTech\Comment\Models\Comment;
use NewsTech\Core\Repositories\BaseRepository;

/**
 * @extends BaseRepository<Comment>
 */
class CommentRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Comment::class;
    }

    /**
     * @return Builder<Comment>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()
            ->with(['article:id,title,slug', 'moderator:id,name,email', 'reader:id,name,email'])
            ->latest('id');
    }

    /**
     * @return Collection<int, Comment>
     */
    public function approvedForArticle(Article $article): Collection
    {
        return $this->query()
            ->with([
                'reader:id,name,email',
                'children' => fn ($query) => $query
                    ->with('reader:id,name,email')
                    ->where('status', 'approved')
                    ->oldest('approved_at')
                    ->oldest('id'),
            ])
            ->whereBelongsTo($article)
            ->where('status', 'approved')
            ->whereNull('parent_id')
            ->oldest('approved_at')
            ->oldest('id')
            ->get();
    }

    public function approvedCountForArticle(Article $article): int
    {
        return $this->query()
            ->whereBelongsTo($article)
            ->where('status', 'approved')
            ->count();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForArticle(Article $article, array $attributes): Comment
    {
        /** @var Comment $comment */
        $comment = $this->create([
            ...$attributes,
            'article_id' => $article->getKey(),
        ]);

        return $comment;
    }

    public function hasRecentSubmission(Article $article, string $email, ?string $ipAddress, int $throttleSeconds): bool
    {
        $threshold = Carbon::now()->subSeconds($throttleSeconds);

        return $this->query()
            ->whereBelongsTo($article)
            ->where('created_at', '>=', $threshold)
            ->where(function (Builder $query) use ($email, $ipAddress): void {
                $query->where('email', $email);

                if ($ipAddress) {
                    $query->orWhere('ip_address', $ipAddress);
                }
            })
            ->exists();
    }

    public function approve(Comment $comment, ?int $moderatedBy = null): Comment
    {
        /** @var Comment $comment */
        $comment = $this->update($comment, [
            'status' => 'approved',
            'is_spam' => false,
            'spam_reason' => null,
            'approved_at' => now(),
            'moderated_at' => now(),
            'moderated_by' => $moderatedBy,
        ]);

        return $comment;
    }

    public function reject(Comment $comment, ?int $moderatedBy = null): Comment
    {
        /** @var Comment $comment */
        $comment = $this->update($comment, [
            'status' => 'rejected',
            'approved_at' => null,
            'moderated_at' => now(),
            'moderated_by' => $moderatedBy,
        ]);

        return $comment;
    }
}
