<?php

namespace ByErikas\EloquentBigQuery;

use ByErikas\EloquentBigQuery\Exceptions\WheresCantBeEmpty;
use ByErikas\EloquentBigQuery\Traits\BuildsSQLStatements;
use Illuminate\Support\Carbon;

class Where
{
    use BuildsSQLStatements;

    private array $wheres = [];

    public function where(string $column, mixed $operator, mixed $value = null, string $boolean = "and"): self
    {
        if (!count($this->wheres)) {
            $boolean = "";
        }

        $this->wheres[] = $this->buildWhere($column, $operator, $value, $boolean);

        return $this;
    }

    public function whereIn(string $column, array $values, string $boolean = "and"): self
    {
        if (empty($values)) {
            return $this;
        }

        if (!count($this->wheres)) {
            $boolean = "";
        }

        $this->wheres[] = $this->buildWhereIn($column, $values, $boolean);

        return $this;
    }

    public function whereBetween(string $column, int|string|Carbon $start, int|string|Carbon $end, string $boolean = "and"): self
    {
        if (!count($this->wheres)) {
            $boolean = "";
        }

        $this->wheres[] = $this->buildWhereBetween($column, $start, $end, $boolean);

        return $this;
    }

    public function toSQL(): string
    {
        if (empty($this->wheres)) {
            throw new WheresCantBeEmpty();
        }

        return "({$this->buildWheres()})";
    }
}
