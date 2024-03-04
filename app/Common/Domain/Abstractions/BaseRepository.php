<?php

namespace App\Common\Domain\Abstractions;

use App\Common\Domain\Contracts\CriteriaHandlerInterface;
use App\Common\Domain\Contracts\CriteriaInterface;
use App\Common\Domain\Contracts\OrderHandlerInterface;
use App\Common\Domain\Contracts\OrderInterface;
use App\Common\Domain\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Container\Container as App;
abstract class BaseRepository implements RepositoryInterface, CriteriaHandlerInterface, OrderHandlerInterface
{
    /**
     * The underlying model class name
     * @var string
     */
    protected Model $model;

    /**
     * @var Builder
     */
    protected Builder $builder;

    /**
     * Switch to skip criteria
     * @var bool
     */
    protected bool $skipCriteria = false;
    protected bool $skipOrders = false;
    /**
     * Prevent overwriting same criteria in stack
     * @var bool
     */
    protected bool $preventCriteriaOverwriting = true;

    /**
     * Specify the underlying model class
     * @return string
     */
    abstract public function model(): string;

    /**
     * @param App $app
     * @param Collection $criteria
     */
    public function __construct(
        protected readonly App $app,
        /**
         * The current stack of criteria
         * @var Collection
         */
        protected Collection   $criteria = new Collection(),
        protected Collection   $order = new Collection(),
    )
    {
        $this->resetScope();
        $this->makeModel();
        $this->makeSelect();
    }

    private function makeSelect(): void
    {
        $this->builder->select(app($this->model())->getTable() . '.*');
    }

    private function makeModel(): void
    {
        $this->model = app($this->model());
    }

    private function resetScope(): void
    {
        $this->builder = $this->model()::query();
    }


    public function all(array $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyOrder();
        return $this->builder->get($columns);
    }

    public function query(): Builder
    {
        return $this->builder;
    }

    public function paginate(int $perPage = 1, array $columns = ['*'], $pageName = 'page', int $page = 1): Paginator
    {
        $this->applyCriteria();
        $this->applyOrder();
        return $this->builder->paginate($perPage, ['*'], $pageName, $page);
    }

    public function find(int $id, array $columns)
    {
        $this->applyCriteria();
        $this->applyOrder();
        return $this->builder->find($id, $columns);
    }

    public function findBy(string $field, $value, $columns = ["*"])
    {
        $this->applyCriteria();
        $this->applyOrder();
        return $this->builder->where($field, '=', $value)->first($columns);
    }

    public function findAllBy(string $field, $value, $columns = ["*"])
    {
        $this->applyCriteria();
        $this->applyOrder();
        return $this->builder->where($field, '=', $value)->all($columns);
    }

    public function findWhere(string $where, $columns = ["*"])
    {
        $this->applyCriteria();
        $this->applyOrder();
        return $this->builder->where($where)->all($columns);
    }

    public function findOrFail(int $id, $columns = ["*"])
    {
        $this->applyCriteria();
        $this->applyOrder();
        return $this->builder->findOrFail($id, $columns);
    }

    public function applyCriteria(): self
    {
        if ($this->skipCriteria === true) {
            return $this;
        }
        foreach ($this->getCriteria() as $criteria) {
            if ($criteria instanceof CriteriaInterface) {
                $criteria->apply($this->model, $this);
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

    public function skipCriteria(bool $status = true): void
    {
        $this->skipCriteria = $status;
    }

    public function getByCriteria(CriteriaInterface $criteria): Collection
    {
        $this->pushCriteria($criteria);
        return $this->all();
    }

    public function pushCriteria(CriteriaInterface $criteria): Collection
    {
        $this->criteria->push($criteria);
        return $this->criteria;
    }

    public function pushOrder(OrderInterface $order): Collection
    {
        $this->order->push($order);
        return $this->order;
    }

    #[\Override]
    public function skipOrders(bool $status = true): void
    {
        $this->skipOrders = $status;
    }

    #[\Override]
    public function getOrders(): Collection
    {
        return $this->order;
    }

    #[\Override]
    public function applyOrder(): self
    {
        if ($this->skipOrders === true) {
            return $this;
        }
        foreach ($this->getOrders() as $order) {
            if ($order instanceof OrderInterface) {
                $order->apply($this);
            }
        }
        return $this;
    }

}

