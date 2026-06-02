<?php

namespace NewsTech\Reader\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Reader\Models\Reader;

/**
 * @extends BaseRepository<Reader>
 */
class ReaderRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Reader::class;
    }

    /**
     * @return Builder<Reader>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()
            ->withCount(['comments', 'bookmarks'])
            ->latest('id');
    }

    public function findActiveByEmail(string $email): ?Reader
    {
        /** @var ?Reader $reader */
        $reader = $this->query()
            ->where('email', $email)
            ->where('is_active', true)
            ->first();

        return $reader;
    }

    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderedQuery()->paginate($perPage);
    }
}
