<?php

namespace ByErikas\EloquentBigQuery\Traits;

use ByErikas\EloquentBigQuery\Where;
use Closure;
use Illuminate\Support\Carbon;

trait BuildsWheres
{
    use EscapesProperties;

    private function buildWhere(string|Closure $column, mixed $operator = null, mixed $value = null, ?string $boolean = "and", string $type = "auto"): string
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

        $isOperator = $this->containsComparisonOperator($operator);

        $actualValue = $value;

        if (!$isOperator) {
            $actualValue = $operator;
        }

        $actualValue = $this->escape($actualValue, $type);

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

    private function buildWhereIn(string $column, array $values, ?string $boolean = "and", string $type = "auto"): string
    {
        if ($boolean) {
            $column = strtoupper($boolean) . " {$column}";
        }

        $values = $this->escape($values, $type);

        return "{$column} IN (" . implode(", ", $values) . ")";
    }

    private function buildWhereBetween(string $column, int|string|Carbon $start, int|string|Carbon $end, ?string $boolean = "and", string $type = "auto"): string
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

        $start = $this->escape($start, $type);
        $end = $this->escape($end, $type);

        return "{$column} BETWEEN {$start} AND {$end}";
    }

    private function buildWhereLike(string $column, mixed $value = null, ?string $boolean = "and", string $type = "auto"): string
    {
        if ($boolean) {
            $column = strtoupper($boolean) . " {$column}";
        }

        $value = $this->escape($value, $type);

        return "{$column} LIKE {$value}";
    }

    private function buildWhereLikeAny(string $column, array $value, ?string $boolean = "and", string $type = "auto"): string
    {
        if ($boolean) {
            $column = strtoupper($boolean) . " {$column}";
        }

        $value = implode(", ", $this->escape($value, $type));

        return "{$column} LIKE ANY ({$value})";
    }
}
