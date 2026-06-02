<?php

namespace NewsTech\Media\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NewsTech\Core\Repositories\BaseRepository;
use NewsTech\Media\Models\Media;

/**
 * @extends BaseRepository<Media>
 */
class MediaRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Media::class;
    }

    /**
     * @return Builder<Media>
     */
    public function orderedQuery(): Builder
    {
        return $this->query()->latest('id');
    }
}
