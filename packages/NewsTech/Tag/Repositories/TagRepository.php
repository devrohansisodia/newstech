<?php

namespace NewsTech\Tag\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Tag\Models\Tag;

/**
 * @extends BaseRepository<Tag>
 */
class TagRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Tag::class;
    }

    /**
     * @return Builder<Tag>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()->orderBy('name');
    }
}
