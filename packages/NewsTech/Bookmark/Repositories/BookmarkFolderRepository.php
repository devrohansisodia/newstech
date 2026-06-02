<?php

namespace NewsTech\Bookmark\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use NewsTech\Bookmark\Models\BookmarkFolder;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Reader\Models\Reader;

/**
 * @extends BaseRepository<BookmarkFolder>
 */
class BookmarkFolderRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return BookmarkFolder::class;
    }

    /**
     * @return Builder<BookmarkFolder>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()->withCount('bookmarks')->orderBy('sort_order')->orderBy('name');
    }

    public function createForReader(Reader $reader, string $name): BookmarkFolder
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while ($this->query()->whereBelongsTo($reader)->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        /** @var BookmarkFolder $folder */
        $folder = $this->create([
            'reader_id' => $reader->getKey(),
            'name' => $name,
            'slug' => $slug,
            'sort_order' => 0,
        ]);

        return $folder;
    }

    public function findForReaderBySlug(Reader $reader, string $slug): ?BookmarkFolder
    {
        /** @var ?BookmarkFolder $folder */
        $folder = $this->query()->whereBelongsTo($reader)->where('slug', $slug)->first();

        return $folder;
    }
}
