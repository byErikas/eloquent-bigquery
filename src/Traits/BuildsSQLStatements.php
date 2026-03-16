<?php

namespace ByErikas\EloquentBigQuery\Traits;

use ByErikas\EloquentBigQuery\Exceptions\InvalidSelect;
use ByErikas\EloquentBigQuery\Exceptions\UndefinedMetric;
use ByErikas\EloquentBigQuery\Facades\MetricsRepository;
use ByErikas\EloquentBigQuery\Join;
use ByErikas\EloquentBigQuery\Where;
use Closure;
use Illuminate\Support\Carbon;

trait BuildsSQLStatements
{
    private const array COMPARISON_OPERATORS = ["=", "!=", ">", ">=", "<", "<=", "<>", "LIKE", "NOT LIKE"];
    private const string ACCESS_OPERATOR = ".";

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

            if ($boolean) {
                return strtoupper($boolean) . " {$where->toSQL()}";
            }

            return $where->toSQL();
        }

        if ($boolean) {
            $column = strtoupper($boolean) . " {$column}";
        }

        $isOperator = in_array(strtoupper($operator), self::COMPARISON_OPERATORS);

        $actualValue = $value;

        if (!$isOperator) {
            $actualValue = $operator;
        }

        $shouldEscapeValue = !str_contains($actualValue, self::ACCESS_OPERATOR)
            && !is_numeric($actualValue)
            && !is_null($actualValue)
            && !is_bool($actualValue);

        if ($shouldEscapeValue) {
            $actualValue = "\"{$actualValue}\"";
        }

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

    private function buildWhereBetween(string $column, string|Carbon $start, string|Carbon $end, ?string $boolean = "and"): string
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

        return "{$column} BETWEEN \"{$start}\" AND \"{$end}\"";
    }

    private function buildJoin(string $table, string $alias, Closure $closure, string $type = "inner"): Join
    {
        $join = new Join($table, $alias, $type);

        $closure($join);

        return $join;
    }

    private function buildSelect(): string
    {
        $select = $this->select;

        if (count($this->selectMetrics) > 0) {
            if ($select == ["*"]) {
                throw new InvalidSelect("Select can't be \"*\" when using \"selectMetrics\".");
            }

            foreach ($this->selectMetrics as $metricKeyword) {
                $metric = MetricsRepository::find($metricKeyword);

                if (!$metric) {
                    throw new UndefinedMetric("Metric \"{$metricKeyword}\" not found!");
                }

                $select[] = "{$metric["formula"]} AS {$metricKeyword}";
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

    private function buildOrder(): ?string
    {
        if (is_null($this->orderByColumn)) {
            return null;
        }

        $sql = "ORDER BY {$this->orderByColumn}";

        if (!is_null($this->orderByDirection)) {
            $sql .= " " . strtoupper($this->orderByDirection);
        }

        return $sql;
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
