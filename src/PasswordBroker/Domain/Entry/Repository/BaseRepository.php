<?php

namespace PasswordBroker\Domain\Entry\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use PasswordBroker\Domain\Entry\Contracts\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * Service container
     * @var App
     */
    private App $app;
    /**
     * The underlying model class name
     * @var string
     */
    protected string $model;
    /**
     * The current stack of criteria
     * @var Collection
     */
    protected Collection $criteria;
    /**
     * Switch to skip criteria
     * @var bool
     */
    protected bool $skipCriteria = false;
    /**
     * Prevent overwriting same criteria in stack
     * @var bool
     */
    protected bool $preventCriteriaOverwriting = true;

    /**
     * Specify the underlying model class
     * @return Model
     */
    abstract public function model(): Model;

    public function __construct(App $app, Collection $collection)
    {
        $this->app = $app;
        $this->criteria = $collection;
        $this->resetScope();
        $this->makeModel();
    }


    public function all(array $columns = ['*'])
    {
        $this->applyCriteria();
        return $this->model->get($columns);
    }

    public function query(): string
    {
        return $this->model;
    }


    public function paginate(int $perPage = 1, array $columns = ['*'])
    {
        // TODO: Implement paginate() method.
    }

    public function find(int $id, array $columns)
    {
        $this->applyCriteria();
        return $this->model->find($id, $columns);
    }

    public function findBy(string $field, $value, $columns = ["*"])
    {
        $this->applyCriteria();
        return $this->model->where($field, '=', $value)->first($columns);
    }

    public function findAllBy(string $field, $value, $columns = ["*"])
    {
        $this->applyCriteria();
        return $this->model->where($field, '=', $value)->all($columns);
    }

    public function findWhere(string $where, $columns = ["*"])
    {
        $this->applyCriteria();
        return $this->model->where($where)->all($columns);
    }

    public function findOrFail(int $id, $columns = ["*"])
    {
        $this->applyCriteria();
        return $this->model->findOrFail($id, $columns);
    }

    public function applyCriteria(): self
    {
        if ($this->skipCriteria === true) {
            return $this;
        }
        foreach ($this->getCriteria() as $criteria) {
            if ($criteria instanceof Criteria) {
                $this->model = $criteria->apply(
                    $this->model, $this);
            }
        }
        return $this;
    }

    /**
     * @return Collection
     */
    public function getCriteria(): Collection
    {
        return $this->criteria;
    }


}
