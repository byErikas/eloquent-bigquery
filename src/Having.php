<?php

namespace ByErikas\EloquentBigQuery;

use ByErikas\EloquentBigQuery\Exceptions\HavingsCantBeEmpty;
use ByErikas\EloquentBigQuery\Traits\BuildsSQLStatements;

class Having
{
    use BuildsSQLStatements;

    private array $havings = [];

    public function having(string $column, mixed $operator = null, mixed $value = null, string $boolean = "and"): self
    {
        if (!count($this->havings)) {
            $boolean = "";
        }

        $this->havings[] = $this->buildHaving($column, $operator, $value, $boolean);

        return $this;
    }

    public function havingAggregation(string $aggregation, mixed $operator = null, mixed $value = null, string $boolean = "and"): self
    {
        if (!count($this->havings)) {
            $boolean = "";
        }

        $this->havings[] = $this->buildHavingAggregation($aggregation, $operator, $value, $boolean);

        return $this;
    }

    public function toSQL(): string
    {
        if (empty($this->havings)) {
            throw new HavingsCantBeEmpty();
        }

        return "({$this->buildHavings()})";
    }
}
