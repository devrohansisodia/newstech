<?php

namespace NewsTech\Comment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use NewsTech\Article\Repositories\ArticleRepository;
use NewsTech\Comment\Http\Requests\StoreCommentRequest;
use NewsTech\Comment\Repositories\CommentRepository;
use NewsTech\Comment\Support\CommentSpamChecker;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentController
{
    public function __construct(
        protected ArticleRepository $articles,
        protected CommentRepository $comments,
        protected CommentSpamChecker $spamChecker,
    ) {}

    /**
     * @throws NotFoundHttpException
     */
    public function store(StoreCommentRequest $request, string $slug): RedirectResponse
    {
        $article = $this->articles->findPublishedBySlug($slug);
        $validated = $request->validated();
        $reader = auth(config('newstech-reader.auth.guard'))->user();

        if (! $article) {
            throw new NotFoundHttpException;
        }

        $parentComment = null;

        if (filled($validated['parent_id'] ?? null)) {
            $parentComment = $this->comments->find($validated['parent_id']);

            if (
                ! $parentComment
                || $parentComment->article_id !== $article->getKey()
                || $parentComment->status !== 'approved'
                || $parentComment->deleted_at !== null
                || $parentComment->parent_id !== null
            ) {
                throw ValidationException::withMessages([
                    'content' => 'The selected parent comment is no longer available for replies.',
                ]);
            }
        }

        $honeypotField = (string) config('newstech-comment.honeypot_field', 'company');
        $decision = $this->spamChecker->evaluate($article, [
            ...collect($validated)->except([$honeypotField])->all(),
            'ip_address' => $request->ip(),
            'user_agent' => str((string) $request->userAgent())->limit(1000)->toString(),
            'honeypot' => (string) $request->input($honeypotField),
            'reader' => $reader,
        ]);

        if ($decision->storeComment) {
            $commentAttributes = [
                ...collect($validated)->except([$honeypotField, 'website'])->all(),
                'reader_id' => $reader?->getKey(),
                'parent_id' => $parentComment?->getKey(),
                'name' => $reader?->name ?? $validated['name'],
                'email' => $reader?->email ?? $validated['email'],
                'website' => config('newstech-comment.website_field_enabled', true)
                    ? ($validated['website'] ?? null)
                    : null,
                'ip_address' => $request->ip(),
                'user_agent' => str((string) $request->userAgent())->limit(1000)->toString(),
                'status' => $decision->status,
                'is_spam' => $decision->isSpam,
                'spam_reason' => $decision->spamReason,
                'approved_at' => $decision->status === 'approved' ? now() : null,
            ];

            $this->comments->createForArticle($article, $commentAttributes);
        }

        if (! $decision->accepted) {
            throw ValidationException::withMessages([
                'content' => $decision->publicError ?? 'Your comment could not be accepted.',
            ]);
        }

        return back()->with('comment_status', $decision->successMessage ?? 'Your comment has been submitted.');
    }
}
