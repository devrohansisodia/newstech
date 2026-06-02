@include('newstech-admin::articles._form', [
    'article' => $article,
    'action' => route('admin.newstech.articles.store'),
    'categoryTree' => $categoryTree,
    'authorOptions' => $authorOptions,
    'tagOptions' => $tagOptions,
    'selectedCategoryIds' => $selectedCategoryIds,
    'selectedTagIds' => $selectedTagIds,
    'submitLabel' => 'Create Article',
    'pageTitle' => 'Create Article',
    'pageDescription' => 'Write and publish a new article.',
])
