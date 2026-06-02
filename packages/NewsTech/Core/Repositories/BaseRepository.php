<?php

namespace NewsTech\Core\Repositories;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class BaseRepository
{
    public function __construct(protected Container $container) {}

    /**
     * @return class-string<TModel>
     */
    abstract protected function modelClass(): string;

    /**
     * @return TModel
     */
    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    /**
     * @return Builder<TModel>
     */
    public function query(): Builder
    {
        return $this->newModelInstance()->newQuery();
    }

    /**
     * @return TModel|null
     */
    public function find(int|string $id): ?Model
    {
        return $this->query()->find($id);
    }

    /**
     * @return TModel
     */
    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * @param  TModel|int|string  $model
     * @return TModel
     */
    public function update(Model|int|string $model, array $attributes): Model
    {
        $modelInstance = $this->resolveModel($model);

        $modelInstance->fill($attributes);
        $modelInstance->save();

        return $modelInstance->refresh();
    }

    /**
     * @param  TModel|int|string  $model
     */
    public function delete(Model|int|string $model): bool
    {
        $modelInstance = $this->resolveModel($model);

        return (bool) $modelInstance->delete();
    }

    /**
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns);
    }

    /**
     * @return TModel
     */
    protected function newModelInstance(): Model
    {
        /** @var TModel $model */
        $model = $this->container->make($this->modelClass());

        return $model;
    }

    /**
     * @param  TModel|int|string  $model
     * @return TModel
     */
    protected function resolveModel(Model|int|string $model): Model
    {
        if ($model instanceof Model) {
            return $model;
        }

        return $this->findOrFail($model);
    }
}
