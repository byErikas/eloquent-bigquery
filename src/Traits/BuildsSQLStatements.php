<?php

namespace ByErikas\EloquentBigQuery\Traits;

use ByErikas\EloquentBigQuery\Exceptions\InvalidSelect;
use ByErikas\EloquentBigQuery\Exceptions\UndefinedAggregation;
use ByErikas\EloquentBigQuery\Facades\AggregationsRepository;
use ByErikas\EloquentBigQuery\Join;
use ByErikas\EloquentBigQuery\Where;
use Closure;
use Illuminate\Support\Carbon;

trait BuildsSQLStatements
{
    use EscapesProperties;

    private const array COMPARISON_OPERATORS = ["=", "!=", ">", ">=", "<", "<=", "<>", "LIKE", "NOT LIKE"];

    private function buildFrom(): string
    {
        if (is_object($this->table)) {
            $sql = "({$this->table->toSQL()})";
        } else {
            $sql = "`{$this->table}`";
        }

        if ($this->alias) {
            $sql .= " {$this->alias}";
        }

        return $sql;
    }

    private function buildWhere(string|Closure $column, mixed $operator = null, mixed $value = null, ?string $boolean = "and"): string
    {
        if ($column instanceof Closure) {
            $where = new Where();

            $column($where);

            $result = $where->toSQL();

            if ($boolean) {
                $result = strtoupper($boolean) . " {$result}";
            }

            return $result;
        }

        if ($boolean) {
            $column = strtoupper($boolean) . " {$column}";
        }

        $isOperator = in_array(strtoupper($operator), self::COMPARISON_OPERATORS);

        $actualValue = $value;

        if (!$isOperator) {
            $actualValue = $operator;
        }

        $actualValue = $this->escape($actualValue);

        if (!$isOperator) {
            return match ($actualValue) {
                null => "{$column} IS NULL",
                default => "{$column} = {$actualValue}"
            };
        }

        return "{$column} {$operator} {$actualValue}";
    }

    private function buildWhereNotNull(string $column, ?string $boolean = "and"): string
    {
        if ($boolean) {
            $column = strtoupper($boolean) . " {$column}";
        }

        return "{$column} IS NOT NULL";
    }

    private function buildWhereIn(string $column, array $values, ?string $boolean = "and"): string
    {
        if ($boolean) {
            $column = strtoupper($boolean) . " {$column}";
        }

        $values = array_map(function (mixed $value) {
            if (is_numeric($value)) {
                return $value;
            }

            return "'{$value}'";
        }, $values);

        return "{$column} IN (" . implode(", ", $values) . ")";
    }

    private function buildWhereBetween(string $column, int|string|Carbon $start, int|string|Carbon $end, ?string $boolean = "and"): string
    {
        if ($boolean) {
            $column = strtoupper($boolean) . " {$column}";
        }

        if (is_object($start) && $start instanceof Carbon) {
            $start = $start->format("Y-m-d H:i:s");
        }

        if (is_object($end) && $end instanceof Carbon) {
            $end = $end->format("Y-m-d H:i:s");
        }

        $start = $this->escape($start);
        $end = $this->escape($end);

        return "{$column} BETWEEN {$start} AND {$end}";
    }

    private function buildJoin(string $table, string $alias, Closure $closure, string $type = "inner"): Join
    {
        $join = new Join($table, $alias, $type);

        $closure($join);

        return $join;
    }

    private function buildHavingAggregation(string $aggregation, mixed $operator = null, mixed $value = null, ?string $boolean = "and"): string
    {
        $aggregationData = AggregationsRepository::find($aggregation);

        if (!$aggregationData || !isset($aggregationData["value"])) {
            throw new UndefinedAggregation("Aggregation \"{$aggregation}\" not found, or is of invalid format!");
        }

        $column = $aggregation["value"];

        if ($boolean) {
            $column = strtoupper($boolean) . " {$aggregation["value"]}";
        }

        $isOperator = in_array(strtoupper($operator), self::COMPARISON_OPERATORS);

        $actualValue = $value;

        if (!$isOperator) {
            $actualValue = $operator;
        }

        $actualValue = $this->escape($actualValue);

        if (!$isOperator) {
            return match ($actualValue) {
                null => "{$column} IS NULL",
                default => "{$column} = {$actualValue}"
            };
        }

        return "{$column} {$operator} {$actualValue}";
    }

    private function buildSelect(): string
    {
        $select = $this->select;

        if (count($this->selectAggregations) > 0) {
            if ($select == ["*"]) {
                throw new InvalidSelect("Select can't be \"*\" when using \"selectAggregations\".");
            }

            foreach ($this->selectAggregations as $metricKeyword) {
                $metric = AggregationsRepository::find($metricKeyword);

                if (!$metric || !isset($metric["value"])) {
                    throw new UndefinedAggregation("Aggregation \"{$metricKeyword}\" not found, or is of invalid format!");
                }

                $select[] = "{$metric["value"]} AS {$metricKeyword}";
            }
        }

        if (empty($select)) {
            throw new InvalidSelect("Select can't be empty.");
        }

        return implode(", ", $select);
    }

    private function buildJoins(): ?string
    {
        $result = [];

        foreach ($this->joins as $join) {
            $result[] = $join->toSQL();
        }

        if (empty($result)) {
            return null;
        }

        return implode(" ", $result);
    }

    private function buildWheres(): ?string
    {
        if (empty($this->wheres)) {
            return null;
        }

        return implode(" ", $this->wheres);
    }

    private function buildGroupBy(): ?string
    {
        if (empty($this->groupBy)) {
            return null;
        }

        return "GROUP BY " . implode(", ", $this->groupBy);
    }

    private function buildHavings(): ?string
    {
        if (empty($this->havings)) {
            return null;
        }

        return implode(" ", $this->havings);
    }

    private function buildOrders(): ?string
    {
        if (empty($this->orders)) {
            return null;
        }

        return "ORDER BY " . implode(", ", $this->orders);
    }

    private function buildLimit(): ?string
    {
        if (is_null($this->limit)) {
            return null;
        }

        return "LIMIT {$this->limit}";
    }

    private function buildOffset(): ?string
    {
        if (is_null($this->offset)) {
            return null;
        }

        return "OFFSET {$this->offset}";
    }
}
