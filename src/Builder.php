<?php

namespace ByErikas\EloquentBigQuery;

use ByErikas\EloquentBigQuery\Traits\BuildsSQLStatements;
use Closure;
use Illuminate\Support\Carbon;

class Builder
{
    use BuildsSQLStatements;

    private Builder|string $table;

    private ?string $alias = null;

    private array $select = [];

    private array $selectMetrics = [];

    private array $wheres = [];

    private array $joins = [];

    private ?int $limit = null;

    private ?int $offset = null;

    private array $groupBy = [];

    private array $orders = [];

    public function __construct(null|Builder|string $table = null, ?string $alias = null)
    {
        if ($table) {
            $this->table = $table;
        }

        $this->alias = $alias;
    }

    public static function table(Builder|string $table, ?string $alias = null): self
    {
        return new self($table, $alias);
    }

    public function select(array $select = []): self
    {
        $this->select = $select;

        return $this;
    }

    public function selectMetrics(array $metrics = []): self
    {
        $this->selectMetrics = $metrics;

        return $this;
    }

    public function where(string|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = "and"): self
    {
        if (!count($this->wheres)) {
            $boolean = "where";
        }

        $this->wheres[] = $this->buildWhere($column, $operator, $value, $boolean);

        return $this;
    }

    public function whereNotNull(string $column, string $boolean = "and"): self
    {
        if (!count($this->wheres)) {
            $boolean = "where";
        }

        $this->wheres[] = $this->buildWhereNotNull($column, $boolean);

        return $this;
    }

    public function whereIn(string $column, array $values, string $boolean = "and"): self
    {
        if (empty($values)) {
            return $this;
        }

        if (!count($this->wheres)) {
            $boolean = "where";
        }

        $this->wheres[] = $this->buildWhereIn($column, $values, $boolean);

        return $this;
    }

    public function whereBetween(string $column, string|Carbon $start, string|Carbon $end, string $boolean = "and"): self
    {
        if (!count($this->wheres)) {
            $boolean = "where";
        }

        $this->wheres[] = $this->buildWhereBetween($column, $start, $end, $boolean);

        return $this;
    }

    public function limit(?int $limit = null): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(?int $offset = null): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function groupBy(array $groupBy = []): self
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    public function orderBy(null|string|array $column = null, ?string $direction = null): self
    {
        if (is_null($column)) {
            $this->orders = [];

            return $this;
        }

        if (is_array($column)) {
            foreach ($column as $orderArray) {
                if (count($orderArray) == 2) {
                    [$col, $dir] = $orderArray;

                    $this->orders[] = "{$col} " . strtoupper($dir);

                    continue;
                }

                $this->orders[] = array_first($orderArray);
            }

            return $this;
        }

        $sql = $column;

        if ($direction) {
            $sql = "{$column} " . strtoupper($direction);
        }

        $this->orders[] = $sql;

        return $this;
    }

    public function join(string $table, string $alias, Closure $closure): self
    {
        $this->joins[] = $this->buildJoin($table, $alias, $closure, "inner");

        return $this;
    }

    public function leftJoin(string $table, string $alias, Closure $closure): self
    {
        $this->joins[] = $this->buildJoin($table, $alias, $closure, "left");

        return $this;
    }

    public function rightJoin(string $table, string $alias, Closure $closure): self
    {
        $this->joins[] = $this->buildJoin($table, $alias, $closure, "right");

        return $this;
    }

    public function crossJoin(string $table, string $alias, Closure $closure): self
    {
        $this->joins[] = $this->buildJoin($table, $alias, $closure, "cross");

        return $this;
    }

    public function fullJoin(string $table, string $alias, Closure $closure): self
    {
        $this->joins[] = $this->buildJoin($table, $alias, $closure, "full");

        return $this;
    }

    public function toSQL(): string
    {
        $sql = "SELECT {$this->buildSelect()} FROM {$this->buildFrom()}";

        $optionals = [
            $this->buildJoins(),
            $this->buildWheres(),
            $this->buildGroupBy(),
            $this->buildOrders(),
            $this->buildLimit(),
            $this->buildOffset(),
        ];

        foreach ($optionals as $optional) {
            if ($optional) {
                $sql .= " {$optional}";
            }
        }

        return $sql;
    }
}
