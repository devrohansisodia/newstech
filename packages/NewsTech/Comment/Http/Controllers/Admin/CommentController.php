<?php

namespace NewsTech\Comment\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NewsTech\Comment\Models\Comment;
use NewsTech\Comment\Repositories\CommentRepository;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;

class CommentController
{
    public function __construct(protected CommentRepository $comments) {}

    public function index(Request $request): View
    {
        $activeFilter = (string) $request->query('filter', 'all');
        $comments = $this->comments->orderedQuery()
            ->when($activeFilter === 'spam', fn ($query) => $query->where('is_spam', true))
            ->when(in_array($activeFilter, ['pending', 'approved', 'rejected'], true), fn ($query) => $query->where('status', $activeFilter))
            ->get();
        $allComments = $this->comments->orderedQuery()->get();

        $dataGrid = DataGridDefinition::make('comments', 'Comments')
            ->description('Moderate reader and guest article comments without changing the public content workflow.')
            ->columns([
                ColumnDefinition::make('article_title', 'Article'),
                ColumnDefinition::make('commenter', 'Commenter'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Approved' => 'success',
                    'Rejected' => 'danger',
                    'Pending' => 'warning',
                ]),
                ColumnDefinition::make('spam_label', 'Spam')->badge(toneMap: [
                    'Flagged' => 'danger',
                    'Clean' => 'success',
                ]),
                ColumnDefinition::make('submitted_at', 'Submitted')->align('right'),
                ColumnDefinition::make('content_preview', 'Comment'),
            ])
            ->rows($comments->map(fn (Comment $comment): array => [
                'id' => $comment->getKey(),
                'article_title' => $comment->article?->title ?? 'Unknown article',
                'commenter' => ($comment->reader ? 'Reader · ' : 'Guest · ').$comment->name.' · '.$comment->email,
                'status_label' => $comment->getStatusLabel(),
                'spam_label' => $comment->is_spam ? 'Flagged' : 'Clean',
                'submitted_at' => $comment->created_at?->format('M d, Y H:i') ?? 'Unknown',
                'content_preview' => ($comment->parent_id ? 'Reply · ' : 'Comment · ').str($comment->content)->limit(90)->toString(),
            ])->all())
            ->rowActions([
                ActionDefinition::make('view', 'View')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.comments.show', $row['id'])),
                ActionDefinition::make('approve', 'Approve')
                    ->usingMethod('PUT')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.comments.approve', $row['id'])),
                ActionDefinition::make('reject', 'Reject')
                    ->usingMethod('PUT')
                    ->tone('neutral')
                    ->url(fn (array $row): string => route('admin.newstech.comments.reject', $row['id'])),
                ActionDefinition::make('delete', 'Delete')
                    ->usingMethod('DELETE')
                    ->tone('danger')
                    ->url(fn (array $row): string => route('admin.newstech.comments.destroy', $row['id'])),
            ])
            ->emptyState(
                'No comments yet.',
                'Guest article comments will appear here once readers begin submitting them from published article pages.'
            );

        return view('newstech-admin::comments.index', [
            'dataGrid' => $dataGrid,
            'activeFilter' => $activeFilter,
            'commentCount' => $allComments->count(),
            'pendingCommentCount' => $allComments->where('status', 'pending')->count(),
            'approvedCommentCount' => $allComments->where('status', 'approved')->count(),
            'rejectedCommentCount' => $allComments->where('status', 'rejected')->count(),
            'spamCommentCount' => $allComments->where('is_spam', true)->count(),
        ]);
    }

    public function show(Comment $comment): View
    {
        $comment->loadMissing('article:id,title,slug', 'moderator:id,name,email', 'reader:id,name,email', 'parent:id,article_id,name');

        return view('newstech-admin::comments.show', [
            'comment' => $comment,
        ]);
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $this->comments->approve($comment, auth('admin')->id());

        return back()->with('comment_status', 'Comment approved successfully.');
    }

    public function reject(Comment $comment): RedirectResponse
    {
        $this->comments->reject($comment, auth('admin')->id());

        return back()->with('comment_status', 'Comment rejected successfully.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->comments->delete($comment);

        return redirect()
            ->route('admin.newstech.comments.index')
            ->with('comment_status', 'Comment deleted successfully.');
    }
}
