<?php

namespace ByErikas\EloquentBigQuery;

use Illuminate\Support\Collection;

class MetricsRepository
{
    private static array $metrics = [];

    public static function from(array|Collection $metrics): void
    {
        if (is_object($metrics) && $metrics instanceof Collection) {
            $metrics = $metrics->all();
        }

        static::$metrics = $metrics;
    }

    public function all(): array
    {
        return static::$metrics;
    }

    public function find(string $keyword): ?array
    {
        return collect(static::$metrics)->firstWhere("keyword", $keyword);
    }
}
