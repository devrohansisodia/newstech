@include('newstech-admin::articles._form', [
    'article' => $article,
    'action' => route('admin.newstech.articles.update', $article),
    'method' => 'PUT',
    'categoryTree' => $categoryTree,
    'authorOptions' => $authorOptions,
    'tagOptions' => $tagOptions,
    'selectedCategoryIds' => $selectedCategoryIds,
    'selectedTagIds' => $selectedTagIds,
    'submitLabel' => 'Update Article',
    'pageTitle' => 'Edit Article',
    'pageDescription' => 'Review content, publishing settings, and category placement.',
])
