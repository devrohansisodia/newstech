<?php

namespace NewsTech\Author\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NewsTech\Author\Models\Author;
use NewsTech\Core\Repositories\BaseRepository;

/**
 * @extends BaseRepository<Author>
 */
class AuthorRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Author::class;
    }

    /**
     * @return Builder<Author>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()->orderBy('name');
    }
}
