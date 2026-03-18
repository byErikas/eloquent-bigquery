<?php

namespace ByErikas\EloquentBigQuery;

use Illuminate\Support\Collection;

class AggregationsRepository
{
    private static array $aggregations = [];

    public static function from(array|Collection $aggregations): void
    {
        if (is_object($aggregations) && $aggregations instanceof Collection) {
            $aggregations = $aggregations->all();
        }

        static::$aggregations = $aggregations;
    }

    public function all(): array
    {
        return static::$aggregations;
    }

    public function find(string $keyword): ?array
    {
        return collect(static::$aggregations)->firstWhere("keyword", $keyword);
    }
}
