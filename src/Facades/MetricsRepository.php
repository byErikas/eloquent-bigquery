<?php

namespace ByErikas\EloquentBigQuery\Facades;

use ByErikas\EloquentBigQuery\MetricsRepository as BigQueryMetricsRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \ByErikas\EloquentBigQuery\QueryService from(array|\Illuminate\Support\Collection $metrics)
 * @method static array all()
 * @method static ?array find($string $keyword)
 *
 * @see \ByErikas\EloquentBigQuery\MetricsRepository
 */
class MetricsRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return BigQueryMetricsRepository::class;
    }
}
