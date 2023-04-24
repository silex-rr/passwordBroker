<?php

namespace App\Common\Domain\Contracts;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

interface RepositoryInterface
{
    public function all(array $columns = ['*']);

    public function paginate(int $perPage = 1, array $columns = ['*'], $pageName = 'page', int $page = 1): Paginator;

    public function find(int $id, array $columns);

    public function findBy(string $field, $value, $columns = ["*"]);

    public function findAllBy(string $field, $value, $columns = ["*"]);

    public function findWhere(string $where, $columns = ["*"]);

    public function findOrFail(int $id, $columns = ["*"]);
    public function query(): Builder;
}
