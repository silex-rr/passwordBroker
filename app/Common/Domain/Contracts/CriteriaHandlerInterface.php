<?php

namespace App\Common\Domain\Contracts;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

interface CriteriaHandlerInterface
{
    /** Skip any applied criteria during processing */
    public function skipCriteria(bool $status = true): void;

    /** Return the currently configured criteria */
    public function getCriteria(): Collection;

    /** Immediately run the passed in criteria and return results */
    public function getByCriteria(CriteriaInterface $criteria): Collection;

    /** Add some criteria to the set of criteria to be applied */
    public function pushCriteria(CriteriaInterface $criteria): Collection;

    /** Apply any pushed criteria */
    public function applyCriteria();
}
