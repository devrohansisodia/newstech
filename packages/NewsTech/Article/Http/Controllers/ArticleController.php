<?php

namespace NewsTech\Article\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use NewsTech\Article\Http\Requests\StoreArticleRequest;
use NewsTech\Article\Http\Requests\UpdateArticleRequest;
use NewsTech\Article\Models\Article;
use NewsTech\Article\Repositories\ArticleRepository;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;

class ArticleController
{
    public function __construct(protected ArticleRepository $articles) {}

    public function index(): View
    {
        $articles = $this->articles->orderedQuery()->get();

        $dataGrid = DataGridDefinition::make('articles', 'Articles')
            ->description('News articles now combine the existing category, author, tag, SEO, and form foundations into a single editorial workflow module.')
            ->columns([
                ColumnDefinition::make('title', 'Title')->sortable(),
                ColumnDefinition::make('slug', 'Slug'),
                ColumnDefinition::make('category', 'Category'),
                ColumnDefinition::make('author', 'Author'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Published' => 'success',
                    'Scheduled' => 'warning',
                    'In Review' => 'primary',
                    'Archived' => 'neutral',
                    'Draft' => 'neutral',
                ]),
                ColumnDefinition::make('view_count', 'Views')->align('right'),
                ColumnDefinition::make('flags', 'Flags'),
                ColumnDefinition::make('published_display', 'Publish Time')->align('right'),
            ])
            ->rows($articles->map(fn (Article $article): array => [
                'id' => $article->getKey(),
                'title' => $article->title,
                'slug' => $article->slug,
                'category' => $article->category?->name ?? 'Unassigned',
                'author' => $article->author?->name ?? 'Unassigned',
                'status_label' => $article->getStatusLabel(),
                'view_count' => number_format($article->view_count),
                'flags' => collect([
                    $article->is_featured ? 'Featured' : null,
                    $article->is_breaking ? 'Breaking' : null,
                    $article->tags->isNotEmpty() ? $article->tags->pluck('name')->join(', ') : null,
                ])->filter()->join(' | ') ?: 'Standard',
                'published_display' => $article->published_at?->format('M d, Y H:i')
                    ?? $article->scheduled_at?->format('M d, Y H:i')
                    ?? 'Not scheduled',
            ])->all())
            ->rowActions([
                ActionDefinition::make('edit', 'Edit')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.articles.edit', $row['id'])),
                ActionDefinition::make('delete', 'Delete')
                    ->usingMethod('DELETE')
                    ->tone('danger')
                    ->url(fn (array $row): string => route('admin.newstech.articles.destroy', $row['id'])),
            ])
            ->emptyState(
                'No articles yet.',
                'Create the first news article to begin connecting categories, authors, tags, and editorial status workflows.'
            );

        return view('newstech-admin::articles.index', [
            'dataGrid' => $dataGrid,
            'articleCount' => $articles->count(),
            'publishedArticleCount' => $articles->where('status', 'published')->count(),
            'scheduledArticleCount' => $articles->where('status', 'scheduled')->count(),
        ]);
    }

    public function create(): View
    {
        return view('newstech-admin::articles.create', [
            'article' => new Article([
                'status' => 'draft',
                'is_featured' => false,
                'is_breaking' => false,
            ]),
            'categoryTree' => $this->articles->categoryTree(),
            'authorOptions' => $this->articles->authorOptions(),
            'tagOptions' => $this->articles->tagOptions(),
            'selectedCategoryIds' => [],
            'selectedTagIds' => [],
        ]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $attributes = $this->normalizedAttributes($request->validated());
        $categoryIds = $request->validated('categories') ?? [];
        $tagIds = $request->validated('tag_ids') ?? [];

        $this->articles->createWithRelations($attributes, $categoryIds, $tagIds);

        return redirect()
            ->route('admin.newstech.articles.index')
            ->with('article_status', 'Article created successfully.');
    }

    public function edit(int|string $article): View
    {
        /** @var Article $article */
        $article = $this->articles->orderedQuery()->findOrFail($article);

        return view('newstech-admin::articles.edit', [
            'article' => $article,
            'categoryTree' => $this->articles->categoryTree(),
            'authorOptions' => $this->articles->authorOptions(),
            'tagOptions' => $this->articles->tagOptions(),
            'selectedCategoryIds' => $article->categories->pluck('id')->all() ?: array_filter([$article->category_id]),
            'selectedTagIds' => $article->tags->pluck('id')->all(),
        ]);
    }

    public function update(UpdateArticleRequest $request, int|string $article): RedirectResponse
    {
        /** @var Article $article */
        $article = $this->articles->findOrFail($article);

        $attributes = $this->normalizedAttributes($request->validated());
        $categoryIds = $request->validated('categories') ?? [];
        $tagIds = $request->validated('tag_ids') ?? [];

        $this->articles->updateWithRelations($article, $attributes, $categoryIds, $tagIds);

        return redirect()
            ->route('admin.newstech.articles.index')
            ->with('article_status', 'Article updated successfully.');
    }

    public function destroy(int|string $article): RedirectResponse
    {
        /** @var Article $article */
        $article = $this->articles->findOrFail($article);

        $article->delete();

        return redirect()
            ->route('admin.newstech.articles.index')
            ->with('article_status', 'Article deleted successfully.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function normalizedAttributes(array $validated): array
    {
        unset($validated['categories'], $validated['tag_ids']);

        if (($validated['status'] ?? null) === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = Carbon::now();
        }

        if (($validated['status'] ?? null) !== 'scheduled') {
            $validated['scheduled_at'] = null;
        }

        if (($validated['status'] ?? null) === 'archived') {
            $validated['published_at'] = $validated['published_at'] ?? null;
        }

        return $validated;
    }
}
