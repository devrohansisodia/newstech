<?php

namespace NewsTech\Bookmark\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use NewsTech\Article\Repositories\ArticleRepository;
use NewsTech\Bookmark\Http\Requests\UpdateBookmarkFolderAssignmentRequest;
use NewsTech\Bookmark\Models\Bookmark;
use NewsTech\Bookmark\Repositories\BookmarkFolderRepository;
use NewsTech\Bookmark\Repositories\BookmarkRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookmarkController extends Controller
{
    public function __construct(
        protected BookmarkRepository $bookmarks,
        protected BookmarkFolderRepository $folders,
        protected ArticleRepository $articles,
    ) {}

    /**
     * @throws NotFoundHttpException
     */
    public function store(string $slug): RedirectResponse
    {
        $reader = auth(config('newstech-reader.auth.guard'))->user();
        $article = $this->articles->findPublishedBySlug($slug);

        if (! $reader || ! $article) {
            throw new NotFoundHttpException;
        }

        $folderId = request()->integer('folder_id') ?: null;
        $folder = null;

        if ($folderId) {
            $folder = $this->folders->query()->whereBelongsTo($reader)->find($folderId);

            if (! $folder) {
                abort(404);
            }
        }
        $alreadySaved = $this->bookmarks->existsForReaderAndArticle($reader, $article);
        $this->bookmarks->createForReaderAndArticle($reader, $article, $folder);

        return back()->with('bookmark_status', $alreadySaved ? 'This article is already saved.' : 'Article saved.');
    }

    /**
     * @throws NotFoundHttpException
     */
    public function destroy(string $slug): RedirectResponse
    {
        $reader = auth(config('newstech-reader.auth.guard'))->user();
        $article = $this->articles->findPublishedBySlug($slug);

        if (! $reader || ! $article) {
            throw new NotFoundHttpException;
        }

        $this->bookmarks->removeForReaderAndArticle($reader, $article);

        return back()->with('bookmark_status', 'Article removed from saved articles.');
    }

    public function index(): View
    {
        $reader = auth(config('newstech-reader.auth.guard'))->user();
        $activeFolder = null;

        if ($reader && filled(request('folder'))) {
            $activeFolder = $this->folders->findForReaderBySlug($reader, (string) request('folder'));
        }

        return view('newstech-frontend::account.bookmarks', [
            'reader' => $reader,
            'activeFolder' => $activeFolder,
            'folders' => $reader ? $this->folders->orderedQuery()->whereBelongsTo($reader)->get() : collect(),
            'bookmarks' => $reader ? $this->bookmarks->paginatePublishedForReader($reader, folder: $activeFolder) : null,
        ]);
    }

    public function updateFolder(UpdateBookmarkFolderAssignmentRequest $request, Bookmark $bookmark): RedirectResponse
    {
        $reader = $request->user(config('newstech-reader.auth.guard'));

        if (! $reader || $bookmark->reader_id !== $reader->getKey()) {
            abort(404);
        }

        $folderId = $request->validated('folder_id');
        $folder = $folderId ? $this->folders->query()->whereBelongsTo($reader)->findOrFail($folderId) : null;

        $this->bookmarks->moveToFolder($bookmark, $folder);

        return back()->with('bookmark_status', 'Bookmark folder updated.');
    }
}
