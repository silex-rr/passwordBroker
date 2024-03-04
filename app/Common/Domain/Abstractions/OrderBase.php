<?php

namespace App\Common\Domain\Abstractions;

use App\Common\Domain\Contracts\ModelFilterableFieldsInterface;
use App\Common\Domain\Contracts\OrderInterface;
use App\Common\Domain\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class OrderBase implements OrderInterface
{
    public final const string ASC = 'asc';
    public final const string DESC = 'desc';
    protected ?array $filterableFields = null;

    protected array $fields = [];
    public function asc(string $filed): void
    {
        $this->fields[$filed] = self::ASC;
    }
    public function desc(string $filed): void
    {
        $this->fields[$filed] = self::DESC;
    }

    public function __construct()
    {
        $model = $this->getModel();
        if ($model instanceof ModelFilterableFieldsInterface) {
            $this->filterableFields = $model->getFilterableFields();
        }
    }
    public function apply(RepositoryInterface $repository): void
    {
        foreach ($this->fields as $field => $direction) {
            if (is_null($this->filterableFields)
                || in_array($field, $this->filterableFields, true)
            ) {
                $repository->query()->orderBy($field, $direction);
            }
        }
    }

    public function getFilterableFields(): ?array
    {
        return $this->filterableFields;
    }

    public function getModel(): ?Model
    {
        return null;
    }
}
